<?php
  /**
   * boost install file for users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function users_install(&$content)
{
    PHPWS_Core::initModClass('users', 'Users.php');
    PHPWS_Core::initModClass('users', 'Action.php');
    PHPWS_Core::configRequireOnce('users', 'config.php');

    if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'branch') {
        Branch::loadHubDB();
        $db = new PHPWS_DB('users');
        $sql = 'select a.password, b.* from user_authorization as a, users as b where b.deity = 1 and a.username = b.username';
        $deities = $db->getAll($sql);

        if (PEAR::isError($deities)) {
            PHPWS_Error::log($deities);
            $content[] = dgettext('users', 'Could not access hub database.');
            return FALSE;
        }
        elseif (empty($deities)) {
            $content[] = dgettext('users', 'Could not find any hub deities.');

            return FALSE;
        } else {
            Branch::restoreBranchDB();
            $auth_db = new PHPWS_DB('user_authorization');
            $user_db = new PHPWS_DB('users');
            $group_db = new PHPWS_DB('users_groups');
            foreach ($deities as $deity) {
                $auth_db->addValue('username', $deity['username']);
                $auth_db->addValue('password', $deity['password']);
                $result = $auth_db->insert();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $content[] = dgettext('users', 'Unable to copy deity login to branch.');
                    continue;
                }
                unset($deity['password']);
                $user_db->addValue($deity);
                $result = $user_db->insert();

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $content[] = dgettext('users', 'Unable to copy deity users to branch.');
                    Branch::loadBranchDB();
                    return FALSE;
                }

                $group_db->addValue('active', 1);
                $group_db->addValue('name', $deity['username']);
                $group_db->addValue('user_id', $result);
                if (PHPWS_Error::logIfError($group_db->insert())) {
                    $content[] = dgettext('users', 'Unable to copy deity user group to branch.');
                    Branch::loadBranchDB();
                    return FALSE;
                }

                $group_db->reset();
                $auth_db->reset();
                $user_db->reset();
            }
            $content[] = dgettext('users', 'Deity users copied to branch.');
        }

        $db = new PHPWS_DB('users_auth_scripts');
        $db->addValue('display_name', dgettext('users', 'Local'));
        $db->addValue('filename', 'local.php');
        $authorize_id = $db->insert();
        if (PEAR::isError($authorize_id)) {
            PHPWS_Error::log($authorize_id);
            $content[] = dgettext('users', 'Unable to create authorization script.');

            return FALSE;
        }

        return TRUE;
    }

    $user = new PHPWS_User;
    $content[] = '<hr />';


    if (isset($_POST['mod_title']) && $_POST['mod_title']=='users'){
        $result = User_Action::postUser($user);
        if (!is_array($result)) {
            $db = new PHPWS_DB('users_auth_scripts');
            $db->addValue('display_name', dgettext('users', 'Local'));
            $db->addValue('filename', 'local.php');
            $authorize_id = $db->insert();

            $user->setDeity(TRUE);
            $user->setActive(TRUE);
            $user->setApproved(TRUE);
            $user->setAuthorize($authorize_id);
            $result = $user->save();
            if (PEAR::isError($result)) {
                return $result;
            }

            PHPWS_Settings::set('users', array('site_contact' => $user->getEmail()));
            PHPWS_Settings::save('users');
            $content[] = dgettext('users', 'User created successfully.');
            $content[] = dgettext('users', 'User\'s email used as contact email address.');
        } else {
            $content[] = userForm($user, $result);

            return FALSE;
        }
    } else {
        $content[] = dgettext('users', 'Please create a user to administrate the site.') . '<br />';
        $content[] = userForm($user);

        return FALSE;
    }

    return TRUE;
}


function userForm(&$user, $errors=NULL){
    PHPWS_Core::initCoreClass('Form.php');
    PHPWS_Core::initModClass('users', 'Form.php');

    $form = new PHPWS_Form;

    if (isset($_REQUEST['module'])) {
        $form->addHidden('module', $_REQUEST['module']);
    } else {
        $form->addHidden('step', 3);
    }

    $form->addHidden('mod_title', 'users');
    $form->addText('username', $user->getUsername());
    $form->addText('email', $user->getEmail());
    $form->addPassword('password1');
    $form->addPassword('password2');

    $form->setLabel('username', dgettext('users', 'Username'));
    $form->setLabel('password1', dgettext('users', 'Password'));
    $form->setLabel('email', dgettext('users', 'Email'));

    $form->addSubmit('go', dgettext('users', 'Add User'));

    $template = $form->getTemplate();

    if (!empty($errors)) {
        foreach ($errors as $tag=>$message) {
            $template[$tag] = $message;
        }
    }

    $result = PHPWS_Template::process($template, 'users', 'forms/userForm.tpl');

    $content[] = $result;
    return implode("\n", $content);
}


?>