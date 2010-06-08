<?php
/**
 * boost install file for users
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function users_install(&$content)
{
    \core\Core::initModClass('users', 'Users.php');
    \core\Core::initModClass('users', 'Action.php');
    \core\Core::configRequireOnce('users', 'config.php');

    if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'branch') {
        $db = new \core\DB;
        \core\Settings::clear();
        if (!createLocalAuthScript()) {
            $content[] = 'Could not create authorization script.';
            return false;
        }
        Branch::loadHubDB();
        $db = new \core\DB('mod_settings');
        $db->addWhere('module', 'users');
        $db->addWhere('setting_name', 'site_contact');
        $db->addColumn('small_char');
        $site_contact = $db->select('one');

        $db = new \core\DB('users');
        $sql = 'select a.password, b.* from user_authorization as a, users as b where b.deity = 1 and a.username = b.username';
        $deities = $db->getAll($sql);

        if (core\Error::isError($deities)) {
            \core\Error::log($deities);
            $content[] = dgettext('users', 'Could not access hub database.');
            return FALSE;
        }
        elseif (empty($deities)) {
            $content[] = dgettext('users', 'Could not find any hub deities.');
            return FALSE;
        } else {
            Branch::restoreBranchDB();
            \core\Settings::set('users', 'site_contact', $site_contact);
            \core\Settings::save('users');
            $auth_db = new \core\DB('user_authorization');
            $user_db = new \core\DB('users');
            $group_db = new \core\DB('users_groups');
            foreach ($deities as $deity) {
                $auth_db->addValue('username', $deity['username']);
                $auth_db->addValue('password', $deity['password']);
                $result = $auth_db->insert();
                if (core\Error::isError($result)) {
                    \core\Error::log($result);
                    $content[] = dgettext('users', 'Unable to copy deity login to branch.');
                    continue;
                }
                unset($deity['password']);
                $user_db->addValue($deity);
                $result = $user_db->insert();

                if (core\Error::isError($result)) {
                    \core\Error::log($result);
                    $content[] = dgettext('users', 'Unable to copy deity users to branch.');
                    Branch::loadBranchDB();
                    return FALSE;
                }

                $group_db->addValue('active', 1);
                $group_db->addValue('name', $deity['username']);
                $group_db->addValue('user_id', $result);
                if (core\Error::logIfError($group_db->insert())) {
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
        return TRUE;
    }

    if (!createLocalAuthScript()) {
        $content[] = 'Could not create local authorization script.';
        return false;
    }

    $authorize_id = \core\Settings::get('users', 'local_script');
    $user = new PHPWS_User;
    $content[] = '<hr />';

    return TRUE;
}


function userForm(&$user, $errors=NULL){
        \core\Core::initModClass('users', 'Form.php');

    $form = new \core\Form;

    if (isset($_REQUEST['module'])) {
        $form->addHidden('module', $_REQUEST['module']);
    } else {
        $form->addHidden('step', 3);
        $form->addHidden('display_name','Install');
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

    $result = \core\Template::process($template, 'users', 'forms/userForm.tpl');

    $content[] = $result;
    return implode("\n", $content);
}

function createLocalAuthScript()
{
    if (core\Settings::get('users', 'local_script')) {
        return true;
    }
    $db = new \core\DB('users_auth_scripts');
    $db->addValue('display_name', dgettext('users', 'Local'));
    $db->addValue('filename', 'local.php');
    $authorize_id = $db->insert();

    if (core\Error::logIfError($authorize_id)) {
        return false;
    }
    \core\Settings::set('users', 'default_authorization', $authorize_id);
    \core\Settings::set('users', 'local_script', $authorize_id);
    \core\Settings::save('users');
    return true;
}

?>