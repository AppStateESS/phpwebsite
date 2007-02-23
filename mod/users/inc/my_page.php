<?php

  /**
   * My Page for users, controls changing password, display name, etc.
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function my_page()
{
    translate('users');
    PHPWS_Core::initModClass('help', 'Help.php');
    if (isset($_REQUEST['subcommand'])) {
        $subcommand = $_REQUEST['subcommand'];
    }
    else {
        $subcommand = 'updateSettings';
    }

    $user = $_SESSION['User'];
    $template['TITLE'] = _('Change my Settings');

    switch ($subcommand){
    case 'updateSettings':

        if (isset($_GET['save'])) {
            $template['MESSAGE'] = _('User settings updated.');
        }

        $content = User_Settings::userForm($user);
        break;

    case 'postUser':
        User_Settings::setTZ();
        User_Settings::setEditor();
        $result = User_Action::postUser($user, FALSE);

        if (is_array($result)) {
            $content = User_Settings::userForm($user, $result);
        }
        else {
            $user->save();
            $_SESSION['User'] = $user;
            PHPWS_Core::reroute('index.php?module=users&action=user&tab=users&save=1');
        }
        break;
    }

    $template['CONTENT'] = $content;
    translate();
    return PHPWS_Template::process($template, 'users', 'my_page/main.tpl'); 
}

class User_Settings {

    function userForm(&$user, $message=NULL){
        $form = new PHPWS_Form;

        $form->addHidden('module', 'users');
        $form->addHidden('action', 'user');
        $form->addHidden('command', 'my_page');
        $form->addHidden('subcommand', 'postUser');

        if (Current_User::allow('users') || $user->display_name == $user->username) {
            $form->addText('display_name', $user->display_name);
            $form->setLabel('display_name', _('Display Name'));
        } else {
            $form->addTplTag('DISPLAY_NAME_LABEL', _('Display Name'));
            $form->addTplTag('DISPLAY_NAME', PHPWS_Help::show_link('users', 'display_name_change', $user->display_name));
        }

        if ($user->canChangePassword()){
            $form->addPassword('password1');
            $form->addPassword('password2');
            $form->setTitle('password2', _('Password confirm'));
            $form->setLabel('password1', _('Password'));
        } else {
            $tpl['PASSWORD1_LABEL'] =  _('Password');
            $tpl['PASSWORD1'] = PHPWS_Help::show_link('users', 'no_password', _('Why can\'t I change my password?'));
        }

        $form->addText('email', $user->getEmail());
        $form->setSize('email', 40);
        $form->setLabel('email', _('Email Address'));

        if (isset($tpl)) {
            $form->mergeTemplate($tpl);
        }

        $tz_list = PHPWS_Time::getTZList();

        $timezones['server'] = _('-- Use server\'s time zone --');
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
        $form->setLabel('timezone', _('Time Zone'));
        $form->setMatch('timezone', $user_tz);

        if (isset($_REQUEST['dst']) && $_REQUEST['timezone'] != 'server') {
            $dst = $_REQUEST['dst'];
        } else {
            $dst = PHPWS_Cookie::read('user_dst');
        }

        $form->addCheckbox('dst', 1);
        $form->setMatch('dst', $dst);
        $form->setLabel('dst', _('Use Daylight Savings Time'));

        $form->addHidden('userId', $user->getId());
        $form->addSubmit('submit', _('Update my information'));

        if (!DISABLE_TRANSLATION && !FORCE_DEFAULT_LANGUAGE) {
            $language_file = PHPWS_Core::getConfigFile('users', 'languages.php');

            if ($language_file) {
                include $language_file;
                $form->addSelect('language', $languages);
                $form->setLabel('language', _('Language preference'));
                if (isset($_COOKIE['phpws_default_language'])) {
                    $language = preg_replace('/\W/', '', $_COOKIE['phpws_default_language']);
                    $form->setMatch('language', $language);
                }
            }
        }

        $editor_list = Editor::getEditorList();
        $all_editors['none'] = _('None');
        foreach ($editor_list as $value) {
            $all_editors[$value] = $value;
        }

        $user_type = Editor::getUserType();
        if (!$user_type) {
            $user_type = 'none';
        }

        $form->addSelect('editor', $all_editors);
        $form->setLabel('editor', _('Preferred editor (admins only)'));
        $form->setMatch('editor', $user_type);

        $template = $form->getTemplate();

        if (isset($message)){
            foreach ($message as $tag=>$error) {
                $template[$tag] = $error;
            }
        }
        return PHPWS_Template::process($template, 'users', 'my_page/user_setting.tpl');
    }

    function setTZ()
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

    function setEditor()
    {
        if (!preg_match('/\W/', $_POST['editor'])) {
            PHPWS_Cookie::write('phpws_editor', $_POST['editor']);
        }
    }
}

?>