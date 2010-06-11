<?php

/**
 * My Page for users, controls changing password, display name, etc.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// Number of days a remember me cookie will last
if (!defined('REMEMBER_ME_LIFE')) {
    define('REMEMBER_ME_LIFE', 365);
}

function my_page()
{
    PHPWS_Core::initModClass('help', 'Help.php');
    if (isset($_REQUEST['subcommand'])) {
        $subcommand = $_REQUEST['subcommand'];
    }
    else {
        $subcommand = 'updateSettings';
    }

    $user = $_SESSION['User'];

    $template['TITLE'] = dgettext('users', 'Change my Settings');
    switch ($subcommand){
        case 'updateSettings':
            if (isset($_GET['save'])) {
                $template['MESSAGE'] = dgettext('users', 'User settings updated.');
            }

            $content = User_Settings::userForm($user);
            break;

        case 'postUser':
            User_Settings::setTZ();
            User_Settings::setEditor();
            User_Settings::rememberMe();
            User_Settings::setCP();
            $result = User_Action::postUser($user, FALSE);

            if (is_array($result)) {
                $content = User_Settings::userForm($user, $result);
            } else {
                if (PHPWS_Error::logIfError($user->save())) {
                    $content = dgettext('users', 'An error occurred while updating your user account.');
                } else {
                    $_SESSION['User'] = $user;
                    PHPWS_Core::reroute('index.php?module=users&action=user&tab=users&save=1');
                }
            }
            break;
    }

    $template['CONTENT'] = $content;

    return PHPWS_Template::process($template, 'users', 'my_page/main.tpl');
}

class User_Settings {

    public static function userForm(PHPWS_User $user, $message=NULL)
    {
        javascript('jquery');
        $form = new PHPWS_Form;

        $form->addHidden('module', 'users');
        $form->addHidden('action', 'user');
        $form->addHidden('command', 'my_page');
        $form->addHidden('subcommand', 'postUser');

        if (Current_User::allow('users') || $user->display_name == $user->username) {
            $form->addText('display_name', $user->display_name);
            $form->setLabel('display_name', dgettext('users', 'Display Name'));
        } else {
            $form->addTplTag('DISPLAY_NAME_LABEL', dgettext('users', 'Display Name'));
            $tpl['DISPLAY_NAME'] = javascript('slider', array('link' => $user->display_name,
                                                              'id'   => 'name-info',
                                                              'message' => dgettext('users', 'Once you change your display name, you may not change it again until reset by the site administrator.')));

            $form->addTplTag('DISPLAY_NAME', PHPWS_Help::show_link('users', 'display_name_change', $user->display_name));
        }

        if ($user->canChangePassword()){
            $form->addPassword('password1');
            $form->setAutoComplete('password1');
            $form->addPassword('password2');
            $form->setAutoComplete('password2');
            $form->setTitle('password2', dgettext('users', 'Password confirm'));
            $form->setLabel('password1', dgettext('users', 'Password'));
        } else {
            $tpl['PASSWORD1_LABEL'] =  dgettext('users', 'Password');
            $tpl['PASSWORD1'] = javascript('slider', array('link' => dgettext('users', 'Why can\'t I change my password?'),
                                                           'id'   => 'pw-info',
                                                           'message' => dgettext('users', 'Your account is authorized external to this site. You will need to update it at the source.')));
        }

        $form->addText('email', $user->getEmail());
        $form->setSize('email', 40);
        $form->setLabel('email', dgettext('users', 'Email Address'));

        if (isset($tpl)) {
            $form->mergeTemplate($tpl);
        }

        $tz_list = PHPWS_Time::getTZList();

        $timezones['server'] = dgettext('users', '-- Use server\'s time zone --');
        foreach ($tz_list as $tz) {
            if (!empty($tz['codes'])) {
                $timezones[$tz['id']] = sprintf('%s : %s', $tz['id'], $tz['codes'][0]);
            } elseif (!empty($tz['city'])) {
                $timezones[$tz['id']] = sprintf('%s : %s', $tz['id'], $tz['city'][0]);
            } else {
                $timezones[$tz['id']] = $tz['id'];
            }

        }

        if (isset($_REQUEST['timezone'])) {
            $user_tz = $_REQUEST['timezone'];
        } else {
            $user_tz = PHPWS_Cookie::read('user_tz');
        }

        $form->addSelect('timezone', $timezones);
        $form->setLabel('timezone', dgettext('users', 'Time Zone'));
        $form->setMatch('timezone', $user_tz);

        if (isset($_REQUEST['dst']) && $_REQUEST['timezone'] != 'server') {
            $dst = $_REQUEST['dst'];
        } else {
            $dst = PHPWS_Cookie::read('user_dst');
        }

        $form->addCheckbox('dst', 1);
        $form->setMatch('dst', $dst);
        $form->setLabel('dst', dgettext('users', 'Use Daylight Savings Time'));

        if (isset($_POST['cp'])) {
            $cp = (int)$_POST['cp'];
        } else {
            $cp = (int)PHPWS_Cookie::read('user_cp');
        }

        $form->addCheckbox('cp', 1);
        $form->setMatch('cp', $cp);
        $form->setLabel('cp', dgettext('users', 'Control Panel flyout menu'));

        if (Current_User::allowRememberMe()) {
            // User must authorize locally
            if ($_SESSION['User']->authorize == 1) {
                $form->addCheckbox('remember_me', 1);
                if (PHPWS_Cookie::read('remember_me')) {
                    $form->setMatch('remember_me', 1);
                }
                $form->setLabel('remember_me', dgettext('users', 'Remember me'));
            }
        }

        $form->addHidden('userId', $user->getId());
        $form->addSubmit('submit', dgettext('users', 'Update my information'));

        if (!DISABLE_TRANSLATION && !FORCE_DEFAULT_LANGUAGE) {
            $language_file = PHPWS_Core::getConfigFile('users', 'languages.php');

            if ($language_file) {
                include $language_file;
                $form->addSelect('language', $languages);
                $form->setLabel('language', dgettext('users', 'Language preference'));
                if (isset($_COOKIE['phpws_default_language'])) {
                    $language = preg_replace('/\W/', '', $_COOKIE['phpws_default_language']);
                    $form->setMatch('language', $language);
                }
            }
        }

        $editor_list = Editor::getEditorList();
        $all_editors['none'] = dgettext('users', 'None');
        foreach ($editor_list as $value) {
            if (Editor::willWork($value)) {
                $all_editors[$value] = $value;
            }
        }

        $user_type = Editor::getUserType();
        if (!$user_type) {
            $user_type = 'none';
        }

        $form->addSelect('editor', $all_editors);
        $form->setLabel('editor', dgettext('users', 'Preferred editor (admins only)'));
        $form->setMatch('editor', $user_type);

        $template = $form->getTemplate();

        if (isset($message)){
            foreach ($message as $tag=>$error) {
                $template[$tag] = $error;
            }
        }

        $template['ACCT_INFO'] = dgettext('users', 'Account Information');
        $template['LOCAL_INFO'] = dgettext('users', 'Localization');
        $template['PREF'] = dgettext('users', 'Preferences');

        return PHPWS_Template::process($template, 'users', 'my_page/user_setting.tpl');
    }

    public function setTZ()
    {
        if ($_POST['timezone'] != 'server' && preg_match('/[^0-9\-]/', $_POST['timezone'])) {
            return;
        }

        if ($_POST['timezone'] == 'server') {
            PHPWS_Cookie::delete('user_tz');
            PHPWS_Cookie::delete('user_dst');
            return;
        } else {
            PHPWS_Cookie::write('user_tz', strip_tags($_POST['timezone']));
        }


        if (isset($_POST['dst'])){
            PHPWS_Cookie::write('user_dst', 1);
        } else {
            PHPWS_Cookie::delete('user_dst');
        }
    }

    public function setCP()
    {
        if (isset($_POST['cp'])) {
            PHPWS_Cookie::write('user_cp', 1);
        } else {
            PHPWS_Cookie::delete('user_cp');
        }
    }

    public function setEditor()
    {
        if (!preg_match('/\W/', $_POST['editor'])) {
            PHPWS_Cookie::write('phpws_editor', $_POST['editor']);
        }
    }

    public function rememberMe()
    {
        // User must authorize locally
        if ( PHPWS_Settings::get('users', 'allow_remember') && $_SESSION['User']->authorize == 1) {
            if (isset($_POST['remember_me'])) {
                $db = new PHPWS_DB('user_authorization');
                $db->addColumn('password');
                $db->addWhere('username', $_SESSION['User']->username);
                $password = $db->select('one');
                if (empty($password)) {
                    return false;
                } elseif (PHPWS_Error::isError($password)) {
                    PHPWS_Error::log($password);
                    return false;
                }

                $remember['username'] = $_SESSION['User']->username;
                $remember['password'] = $password;
                $time_to_live = time() + (86400 * REMEMBER_ME_LIFE);
                PHPWS_Cookie::write('remember_me', serialize($remember), $time_to_live);
            } else {
                PHPWS_Cookie::delete('remember_me');
            }
        }
    }
}

?>