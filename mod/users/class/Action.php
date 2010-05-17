<?php
/**
 * Controls results from forms and administration functions
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';
PHPWS_Core::requireConfig('users');
PHPWS_Core::initModClass('users', 'Form.php');
//PHPWS_Core::initCoreClass('Form.php');


if (!defined('ALLOW_DEITY_FORGET')) {
    define('ALLOW_DEITY_FORGET', false);
}

class User_Action {

    public static function adminAction()
    {
        PHPWS_Core::initModClass('users', 'Group.php');
        $message = $content = null;

        if (!Current_User::allow('users')) {
            PHPWS_User::disallow(dgettext('users', 'Tried to perform an admin function in Users.'));
            return;
        }

        $message = User_Action::getMessage();
        $panel = User_Action::cpanel();
        $panel->enableSecure();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['user_id'])) {
            $user = new PHPWS_User((int)$_REQUEST['user_id']);
        } else {
            $user = new PHPWS_User;
        }
        if (isset($_REQUEST['group_id'])) {
            $group = new PHPWS_Group((int)$_REQUEST['group_id']);
        } else {
            $group = new PHPWS_Group;
        }

        switch ($command) {
            /** Form cases **/
            /** User Forms **/
            case 'new_user':
                if (PHPWS_Settings::get('users', 'allow_new_users') || Current_User::isDeity()) {
                    $panel->setCurrentTab('new_user');
                    $title = dgettext('users', 'Create User');
                    $content = User_Form::userForm($user);
                } else {
                    Current_User::disallow();
                }
                break;

            case 'manage_users':
                $title = dgettext('users', 'Manage Users');
                $content = User_Form::manageUsers();
                break;

            case 'demographics':
                $content = User_Form::demographics();
                break;

            case 'editUser':
                $title = dgettext('users', 'Edit User');
                $user = new PHPWS_User($_REQUEST['user_id']);
                $content = User_Form::userForm($user);
                break;

            case 'notify_user':
                if (!User_Action::notifyUser()) {
                    $message = dgettext('users', 'Failed to send notification email.');
                } else {
                    $message = dgettext('users', 'User created and notified.');
                }
                $user_id = $_SESSION['New_User']['user_id'];
                unset($_SESSION['New_User']);
                User_Action::sendMessage($message, 'setUserPermissions&user_id=' . $user_id);
                break;

            case 'do_not_notify':
                $user_id = $_SESSION['New_User']['user_id'];
                unset($_SESSION['New_User']);
                User_Action::sendMessage(dgettext('users', 'User created.'), 'setUserPermissions&user_id=' . $user_id);
                break;

            case 'deleteUser':
                if (!Current_User::secured('users', 'delete_users')) {
                    Current_User::disallow();
                    return;
                }
                $user->kill();
                PHPWS_Core::goBack();
                break;

            case 'deify_user':
                if (!Current_User::authorized('users') ||
                !Current_User::isDeity()) {
                    Current_User::disallow();
                    return;
                }
                $user->deity = 1;
                $user->save();
                PHPWS_Core::goBack();
                break;

            case 'mortalize_user':
                if (!Current_User::authorized('users') ||
                !Current_User::isDeity()) {
                    Current_User::disallow();
                    return;
                }
                $user->deity = 0;
                $user->save();
                PHPWS_Core::goBack();
                break;


            case 'authorization':
            case 'postAuthorization':
            case 'dropAuthScript':
                if (!Current_User::isDeity()) {
                    Current_User::disallow();
                }

                if ($command == 'dropAuthScript' && isset($_REQUEST['script_id'])) {
                    User_Action::dropAuthorization($_REQUEST['script_id']);
                } elseif ($command == 'postAuthorization') {
                    User_Action::postAuthorization();
                    $message = dgettext('users', 'Authorization updated.');
                }
                $title = dgettext('users', 'Authorization');
                $content = User_Form::authorizationSetup();
                break;

            case 'editScript':
                $title = dgettext('users', 'Edit Authorization Script');
                // no reason to edit scripts yet
                break;

            case 'setUserPermissions':
                if (!Current_User::authorized('users', 'edit_permissions')){
                    PHPWS_User::disallow();
                    return;
                }

                if (!$user->id) {
                    PHPWS_Core::errorPage('404');
                }

                PHPWS_Core::initModClass('users', 'Group.php');
                $title = dgettext('users', 'Set User Permissions') . ' : ' . $user->getUsername();
                $content = User_Form::setPermissions($user->getUserGroup());
                break;

            case 'deactivateUser':
                if (!Current_User::authorized('users')){
                    PHPWS_User::disallow();
                    return;
                }

                User_Action::activateUser($_REQUEST['user_id'], false);
                PHPWS_Core::goBack();
                break;

            case 'activateUser':
                if (!Current_User::authorized('users')){
                    PHPWS_User::disallow();
                    return;
                }

                User_Action::activateUser($_REQUEST['user_id'], true);
                PHPWS_Core::goBack();
                break;

                /** End User Forms **/

                /********************** Group Forms ************************/

            case 'setGroupPermissions':
                if (!Current_User::authorized('users', 'edit_permissions')){
                    PHPWS_User::disallow();
                    return;
                }

                PHPWS_Core::initModClass('users', 'Group.php');
                $title = dgettext('users', 'Set Group Permissions') .' : '. $group->getName();
                $content = User_Form::setPermissions($_REQUEST['group_id'], 'group');
                break;


            case 'new_group':
                $title = dgettext('users', 'Create Group');
                $content = User_Form::groupForm($group);
                break;

            case 'edit_group':
                $title = dgettext('users', 'Edit Group');
                $content = User_Form::groupForm($group);
                break;

            case 'remove_group':
                $group->kill();
                $title = dgettext('users', 'Manage Groups');
                $content = User_Form::manageGroups();
                break;

            case 'manage_groups':
                $panel->setCurrentTab('manage_groups');
                PHPWS_Core::killSession('Last_Member_Search');
                $title = dgettext('users', 'Manage Groups');
                $content = User_Form::manageGroups();
                break;

            case 'manageMembers':
                PHPWS_Core::initModClass('users', 'Group.php');
                $title = dgettext('users', 'Manage Members') . ' : ' . $group->getName();
                $content = User_Form::manageMembers($group);
                break;

            case 'postMembers':
                if (!Current_User::authorized('users', 'add_edit_groups')) {
                    Current_User::disallow();
                    return;
                }

                $title = dgettext('users', 'Manage Members') . ' : ' . $group->getName();
                $content = User_Form::manageMembers($group);
                break;

                /************************* End Group Forms *******************/

                /************************* Misc Forms ************************/
            case 'settings':
                if (!Current_User::authorized('users', 'settings')) {
                    Current_User::disallow();
                    return;
                }

                $title = dgettext('users', 'Settings');
                $content = User_Form::settings();
                break;

                /** End Misc Forms **/

                /** Action cases **/
            case 'deify':
                if (!Current_User::isDeity()) {
                    Current_User::disallow();
                    return;
                }
                $user = new PHPWS_User($_REQUEST['user']);
                if (isset($_GET['authorize'])){
                    if ($_GET['authorize'] == 1 && Current_User::isDeity()){
                        $user->setDeity(true);
                        $user->save();
                        User_Action::sendMessage(dgettext('users', 'User deified.'), 'manage_users');
                        break;
                    } else {
                        User_Action::sendMessage(dgettext('users', 'User remains a lowly mortal.'), 'manage_users');
                        break;
                    }
                } else
                $content = User_Form::deify($user);
                break;

            case 'mortalize':
                if (!Current_User::isDeity()) {
                    Current_User::disallow();
                    return;
                }

                $user = new PHPWS_User($_REQUEST['user']);
                if (isset($_GET['authorize'])){
                    if ($_GET['authorize'] == 1 && Current_User::isDeity()){
                        $user->setDeity(false);
                        $user->save();
                        $content = dgettext('users', 'User transformed into a lowly mortal.') . '<hr />' . User_Form::manageUsers();
                        break;
                    } else {
                        $content = dgettext('users', 'User remains a deity.') . '<hr />' . User_Form::manageUsers();
                        break;
                    }
                } else
                $content = User_Form::mortalize($user);
                break;

            case 'postUser':
                if (isset($_POST['user_id'])) {
                    if (!Current_User::authorized('users', 'edit_users')) {
                        PHPWS_User::disallow();
                        return;
                    }
                } else {
                    // posting new user
                    if (!Current_User::authorized('users')) {
                        PHPWS_User::disallow();
                        return;
                    }
                }

                $result = User_Action::postUser($user);

                if ($result === true){
                    $new_user = !(bool)$user->id;

                    $user->setActive(true);
                    $user->setApproved(true);
                    if (PHPWS_Error::logIfError($user->save())) {
                        $title = dgettext('users', 'Sorry');
                        $content = dgettext('users', 'An error occurred when trying to save the user. Check your logs.');
                        break;
                    }

                    if ($new_user) {
                        User_Action::assignDefaultGroup($user);
                    }

                    $panel->setCurrentTab('manage_users');

                    if (isset($_POST['user_id'])) {
                        User_Action::sendMessage(dgettext('users', 'User updated.'), 'manage_users');
                    } elseif (Current_User::allow('users', 'edit_permissions')) {
                        $title = dgettext('users', 'Notify user');
                        $content = User_Action::askNotify($user);

                    } else {
                        User_Action::sendMessage(dgettext('users', 'User created.'), 'new_user');
                    }
                } else {
                    $message = implode('<br />', $result);
                    if (isset($_POST['user_id'])) {
                        $title = dgettext('users', 'Edit User');
                    }
                    else {
                        $title = dgettext('users', 'Create User');
                    }

                    $content = User_Form::userForm($user);
                }
                break;

            case 'postPermission':
                if (!Current_User::authorized('users', 'edit_permissions')) {
                    PHPWS_User::disallow();
                    return;
                }
                User_Action::postPermission();
                User_Action::sendMessage(dgettext('users', 'Permissions updated'), $panel->getCurrentTab());
                break;

            case 'postGroup':
                if (!Current_User::authorized('users', 'add_edit_groups')) {
                    PHPWS_User::disallow();
                    return;
                }

                PHPWS_Core::initModClass('users', 'Group.php');
                $result = User_Action::postGroup($group);

                if (PHPWS_Error::isError($result)){
                    $message = $result->getMessage();
                    $title = isset($group->id) ? dgettext('users', 'Edit Group') : dgettext('users', 'Create Group');
                    $content = User_form::groupForm($group);
                } else {
                    $result = $group->save();

                    if (PHPWS_Error::logIfError($result)) {
                        $message = dgettext('users', 'An error occurred when trying to save the group.');
                    } else {
                        $message = dgettext('users', 'Group created.');
                    }
                    User_Action::sendMessage($message, 'manage_groups');
                }
                break;


            case 'addMember':
                if (!Current_User::authorized('users', 'add_edit_groups')) {
                    PHPWS_User::disallow();
                    return;
                }

                PHPWS_Core::initModClass('users', 'Group.php');
                $group->addMember($_REQUEST['member']);
                $group->save();
                unset($_SESSION['Last_Member_Search']);
                User_Action::sendMessage(dgettext('users', 'Member added.'), 'manageMembers&group_id=' . $group->id);
                break;

            case 'dropMember':
                if (!Current_User::authorized('users', 'add_edit_groups')) {
                    PHPWS_User::disallow();
                    return;
                }

                PHPWS_Core::initModClass('users', 'Group.php');
                $group->dropMember($_REQUEST['member']);
                $group->save();
                unset($_SESSION['Last_Member_Search']);
                User_Action::sendMessage(dgettext('users', 'Member removed.'), 'manageMembers&group_id=' . $group->id);
                break;

            case 'update_settings':
                if (!Current_User::authorized('users', 'settings')) {
                    PHPWS_User::disallow();
                    return;
                }
                $title = dgettext('users', 'Settings');

                $result = User_Action::update_settings();
                if ($result === true) {
                    $message = dgettext('users', 'User settings updated.');
                } else {
                    $message = $result;
                }
                $content = User_Form::settings();
                break;

            case 'check_permission_tables':
                if (!Current_User::authorized('users', 'settings')) {
                    PHPWS_User::disallow();
                    return;
                }
                $title = dgettext('users', 'Register Module Permissions');
                $content = User_Action::checkPermissionTables();
                break;

            default:
                PHPWS_Core::errorPage('404');
                break;
        }

        $template['CONTENT'] = $content;
        $template['TITLE'] = $title;
        $template['MESSAGE'] = $message;

        $final = PHPWS_Template::process($template, 'users', 'main.tpl');

        $panel->setContent($final);

        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    public static function popupPermission()
    {
        if (!isset($_GET['key_id'])) {
            echo dgettext('users', 'Missing key information.');
        }
        $key = new Key((int)$_GET['key_id']);

        if (!Key::checkKey($key, false)) {
            PHPWS_Error::log(USER_BAD_KEY, 'users', 'User_Action::popupPermission', "Key : " . $_GET['key_id']);
            echo dgettext('users', 'Unable to set permissions. Bad key data.');
            Layout::nakedDisplay();
        }

        if (Current_User::isRestricted($key->module) ||
        !$key->allowEdit()) {
            javascript('close_refresh', array('location'=>'index.php?module=users&action=user&command=login_page'));
            Layout::nakedDisplay();
        }

        $content = User_Action::getPermissionForm($key);
        Layout::nakedDisplay($content);
    }

    public static function getPermissionForm(Key $key)
    {
        if (Current_User::isUnrestricted($key->module) &&
        Current_User::allow($key->module, $key->edit_permission)) {
            $tpl = User_Form::permissionMenu($key, true);

            return PHPWS_Template::process($tpl, 'users', 'forms/permission_pop.tpl');
        }
    }

    public static function permission()
    {
        if (!isset($_REQUEST['key_id'])) {
            return;
        }

        $key = new Key((int)$_REQUEST['key_id']);

        if (!Key::checkKey($key, false)) {
            return;
        }

        if (Current_User::isRestricted($key->module) ||
        !$key->allowEdit()) {
            Current_User::disallow();
        }

        // View permissions must be first to allow error checking
        // Edit will add its list to the view
        Users_Permission::postViewPermissions($key);
        Users_Permission::postEditPermissions($key);

        $result = $key->savePermissions();
        if (isset($_POST['popbox'])) {
            Layout::nakedDisplay(javascript('close_refresh', array('refresh'=>0)));
        } else {
            if (PHPWS_Error::logIfError($result)) {
                $_SESSION['Permission_Message'] = dgettext('users', 'An error occurred.');
            } else {
                $_SESSION['Permission_Message'] = dgettext('users', 'Permissions updated.');
            }
            PHPWS_Core::goBack();
        }
    }


    public static function getMessage()
    {
        if (!isset($_SESSION['User_Admin_Message'])) {
            return null;
        }
        $message = $_SESSION['User_Admin_Message'];
        unset($_SESSION['User_Admin_Message']);
        return $message;
    }

    public function sendMessage($message, $command)
    {
        $_SESSION['User_Admin_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=users&action=admin&command='
        . $command . '&authkey=' . Current_User::getAuthKey());
    }

    /**
     * Checks a new user's form for errors
     */
    public function postNewUser(PHPWS_User $user)
    {
        $new_user_method = PHPWS_User::getUserSetting('new_user_method');

        $result = $user->setUsername($_POST['username']);
        if (PHPWS_Error::isError($result)) {
            $error['USERNAME_ERROR'] = dgettext('users', 'Please try another user name.');
        }

        if (!User_Action::testForbidden($user)) {
            $user->username = null;
            $error['USERNAME_ERROR'] = dgettext('users', 'Please try another user name.');
        }

        if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
            $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

            if (PHPWS_Error::isError($result)) {
                $error['PASSWORD_ERROR'] = $result->getMessage();
            }
            else {
                $user->setPassword($_POST['password1'], false);
            }
        }

        if (empty($_POST['email'])) {
            $error['EMAIL_ERROR'] = dgettext('users', 'Missing an email address.');
        } else {
            $result = $user->setEmail($_POST['email']);
            if (PHPWS_Error::isError($result)) {
                $error['EMAIL_ERROR'] = dgettext('users', 'This email address cannot be used.');
            }
        }

        if (!User_Action::confirm()) {
            $error['CONFIRM_ERROR'] = dgettext('users', 'Confirmation phrase is not correct.');
        }

        if (isset($error)) {
            return $error;
        } else {
            return true;
        }
    }


    public function confirm()
    {
        if (!PHPWS_User::getUserSetting('graphic_confirm') ||
        !extension_loaded('gd')) {
            return true;
        }

        PHPWS_Core::initCoreClass('Captcha.php');
        return Captcha::verify();
    }

    public static function postUser(PHPWS_User $user, $set_username=true)
    {
        if (!$user->id || ($user->authorize == PHPWS_Settings::get('users', 'local_script') && $set_username)) {
            $user->_prev_username = $user->username;
            $result = $user->setUsername($_POST['username']);
            if (PHPWS_Error::isError($result)) {
                $error['USERNAME_ERROR'] = $result->getMessage();
            }

            if ( ($user->_prev_username != $user->username) &&
            (empty($_POST['password1']) || empty($_POST['password2']))) {
                $error['PASSWORD_ERROR'] = dgettext('users', 'Passwords must be reentered on user name change.');
            }
        }

        if (isset($_POST['display_name'])) {
            $result = $user->setDisplayName($_POST['display_name']);
            if (PHPWS_Error::isError($result)) {
                $error['DISPLAY_ERROR'] = $result->getMessage();
            }
        }

        if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
            $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

            if (PHPWS_Error::isError($result)) {
                $error['PASSWORD_ERROR'] = $result->getMessage();
            }
            else {
                $user->setPassword($_POST['password1']);
            }
        }

        $result = $user->setEmail($_POST['email']);
        if (PHPWS_Error::isError($result)) {
            $error['EMAIL_ERROR'] = $result->getMessage();
        }

        if (Current_User::isLogged() &&
        Current_User::allow('users', 'settings') &&
        isset($_POST['authorize'])) {
            $user->setAuthorize($_POST['authorize']);
        }

        if (isset($_POST['language'])) {
            $locale = preg_replace('/\W/', '', $_POST['language']);
            setcookie('phpws_default_language', $locale, time() + CORE_COOKIE_TIMEOUT);
        }

        if (isset($error)) {
            return $error;
        }
        else {
            return true;
        }
    }

    public static function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = PHPWS_Text::linkAddress('users', array('action'=>'admin'),false,false,true,false);

        if (PHPWS_Settings::get('users', 'allow_new_users') || Current_User::isDeity()) {
            $tabs['new_user'] = array('title'=>dgettext('users', 'New User'), 'link'=>$link);
        }

        if (Current_User::allow('users', 'edit_users') || Current_User::allow('users', 'delete_users'))
        $tabs['manage_users'] = array('title'=>dgettext('users', 'Manage Users'), 'link'=>$link);

        if (Current_User::allow('users', 'add_edit_groups')){
            $tabs['new_group'] = array('title'=>dgettext('users', 'New Group'), 'link'=>$link);
            $tabs['manage_groups'] = array('title'=>dgettext('users', 'Manage Groups'), 'link'=>$link);
        }

        if (Current_User::isDeity()) {
            $tabs['authorization'] = array('title'=>dgettext('users', 'Authorization'), 'link'=>$link);
        }

        if (Current_User::allow('users', 'settings')) {
            $tabs['settings'] = array('title'=>dgettext('users', 'Settings'), 'link'=>$link);
        }

        $panel = new PHPWS_Panel('user_user_panel');
        $panel->quickSetTabs($tabs);
        $panel->setModule('users');
        $panel->setPanel('panel.tpl');

        return $panel;
    }

    /**
     * Controller of user requests. Based on the command request variable
     * defaults to my_page
     */
    public static function userAction()
    {
        $auth = Current_User::getAuthorization();
        $content = $title = null;
        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = 'my_page';
        }

        switch ($command) {
            case 'login':
                if ( !Current_User::isLogged() && isset($_POST['phpws_username']) &&
                isset($_POST['phpws_password']) ) {
                    $result = Current_User::loginUser($_POST['phpws_username'], $_POST['phpws_password']);
                    // here

                    if (!$result) {
                        $title = dgettext('users', 'Login page');
                        $message = dgettext('users', 'Username and password combination not found.');
                        $content = User_Form::loginPage();
                    } elseif(PHPWS_Error::isError($result)) {
                        if (preg_match('/L\d/', $result->code)) {
                            $title = dgettext('users', 'Sorry');
                            $content = $result->getMessage();
                            $content .= ' ' . sprintf('<a href="mailto:%s">%s</a>', PHPWS_User::getUserSetting('site_contact'),
                            dgettext('users', 'Contact the site administrator'));
                        } else {
                            PHPWS_Error::log($result);
                            $message = dgettext('users', 'A problem occurred when accessing user information. Please try again later.');
                        }
                    } else {
                        Current_User::getLogin();
                        PHPWS_Core::returnToBookmark();
                    }
                } else {
                    PHPWS_Core::errorPage('403');
                }
                break;

                // reset user password
            case 'rp':
                $user_id = User_Action::checkResetPassword();
                if ($user_id) {
                    $title = dgettext('users', 'Reset my password');
                    $content = User_Form::resetPassword($user_id, $_GET['auth']);
                } else {
                    $title = dgettext('users', 'Sorry');
                    $content = dgettext('users', 'Your password request was not found or timed out. Please apply again.');
                }
                break;

            case 'my_page':
                if ($auth->local_user) {
                    PHPWS_Core::initModClass('users', 'My_Page.php');
                    $my_page = new My_Page;
                    $my_page->main();
                } else {
                    Layout::add(PHPWS_ControlPanel::display(dgettext('users', 'My Page unavailable to remote users.'), 'my_page'));
                }
                break;

            case 'signup_user':
                $title = dgettext('users', 'New Account Sign-up');
                if (Current_User::isLogged()) {
                    $content = dgettext('users', 'You already have an account.');
                    break;
                }
                $user = new PHPWS_User;
                if (PHPWS_User::getUserSetting('new_user_method') == 0) {
                    $content = dgettext('users', 'Sorry, we are not accepting new users at this time.');
                    break;
                }
                $content = User_Form::signup_form($user);
                break;

            case 'submit_new_user':
                $title = dgettext('users', 'New Account Sign-up');
                $user_method = PHPWS_User::getUserSetting('new_user_method');
                if ($user_method == 0) {
                    Current_User::disallow(dgettext('users', 'New user signup not allowed.'));
                    return;
                }

                $user = new PHPWS_User;
                $result = User_Action::postNewUser($user);

                if (is_array($result)) {
                    $content = User_Form::signup_form($user, $result);
                } else {
                    $content = User_Action::successfulSignup($user);
                }
                break;

            case 'logout':
                $auth = Current_User::getAuthorization();
                $auth->logout();
                PHPWS_Core::killAllSessions();
                PHPWS_Core::reroute('index.php?module=users&action=reset');
                break;

            case 'login_page':
                if (Current_User::isLogged()) {
                    PHPWS_Core::home();
                }
                $title = dgettext('users', 'Login Page');
                $content = User_Form::loginPage();
                break;

            case 'confirm_user':
                if (Current_User::isLogged()) {
                    PHPWS_Core::home();
                }
                if (User_Action::confirmUser()) {
                    $title = dgettext('users', 'Welcome!');
                    $content = dgettext('users', 'Your account has been successfully activated. Please log in.');
                } else {
                    $title = dgettext('users', 'Sorry');
                    $content = dgettext('users', 'This authentication does not exist.<br />
 If you did not log in within the time frame specified in your email, please apply for another account.');
                }
                User_Action::cleanUpConfirm();
                break;

            case 'forgot_password':
                if (Current_User::isLogged()) {
                    PHPWS_Core::home();
                }
                $title = dgettext('users', 'Forgot Password');
                $content = User_Form::forgotForm();
                break;

            case 'post_forgot':
                $title = dgettext('users', 'Forgot Password');
                if (ALLOW_CAPTCHA) {
                    PHPWS_Core::initCoreClass('Captcha.php');
                    if (!Captcha::verify()) {
                        $content = dgettext('users', 'Captcha information was incorrect.');
                        $content .= User_Form::forgotForm();
                    } else if (!User_Action::postForgot($content)) {
                        $content .= User_Form::forgotForm();
                    }
                } elseif (!User_Action::postForgot($content)) {
                    $content .= User_Form::forgotForm();
                }

                break;

            case 'reset_pw':
                $pw_result = User_Action::finishResetPW();
                switch ($pw_result) {
                    case PHPWS_Error::isError($pw_result):
                        $title = dgettext('users', 'Reset my password');
                        $content = dgettext('users', 'Passwords were not acceptable for the following reason:');
                        $content .= '<br />' . $pw_result->getmessage() . '<br />';
                        $content .= User_Form::resetPassword($_POST['user_id'], $_POST['authhash']);
                        break;

                    case 0:
                        $title = dgettext('users', 'Sorry');
                        $content = dgettext('users', 'A problem occurred when trying to update your password. Please try again later.');
                        break;

                    case 1:
                        PHPWS_Core::home();
                        break;
                }
                break;

            default:
                PHPWS_Core::errorPage('404');
                break;
        }

        if (isset($message)) {
            $tag['MESSAGE'] = $message;
        }

        if (isset($title)) {
            $tag['TITLE'] = $title;
        }

        if(isset($content)) {
            $tag['CONTENT'] = $content;
        }

        if (isset($tag)) {
            $final = PHPWS_Template::process($tag, 'users', 'user_main.tpl');
            Layout::add($final);
        }
    }

    public function confirmUser()
    {
        $hash = $_GET['hash'];
        if (preg_match('/\W/', $hash)) {
            Security::log(sprintf(dgettext('users', 'User tried to send bad hash (%s) to confirm user.'), $hash));
            PHPWS_Core::errorPage('400');
        }
        $db = new PHPWS_DB('users_signup');
        $db->addWhere('authkey', $hash);
        $row = $db->select('row');

        if (PHPWS_Error::logIfError($row)) {
            return false;
        } elseif (empty($row)) {
            return false;
        } else {
            $user_id = &$row['user_id'];
            $user = new PHPWS_User($user_id);

            // If the deadline has not yet passed, approve the user, save, and return true
            if ($row['deadline'] > time()) {
                $db->delete();
                $user->approved = 1;
                if (PHPWS_Error::logIfError($user->save())) {
                    return false;
                } else {
                    User_Action::assignDefaultGroup($user);
                    return true;
                }
            } else {
                // If the deadline has passed, delete the user and return false.
                $user->delete();
                return false;
            }
        }
    }

    public function cleanUpConfirm()
    {
        $db = new PHPWS_DB('users_signup');
        $db->addWhere('deadline', time(), '<');
        $result = $db->delete();
        PHPWS_Error::logIfError($result);
    }

    public function successfulSignup($user)
    {
        switch (PHPWS_User::getUserSetting('new_user_method')) {
            case AUTO_SIGNUP:
                $result = User_Action::saveNewUser($user, true);
                if ($result) {
                    User_Action::assignDefaultGroup($user);
                    $content[] = dgettext('users', 'Account created successfully!');
                    $content[] = dgettext('users', 'You will return to the home page in five seconds.');
                    $content[] = PHPWS_Text::moduleLink(dgettext('users', 'Click here if you are not redirected.'));
                    Layout::metaRoute();
                } else {
                    $content[] = dgettext('users', 'An error occurred when trying to create your account. Please try again later.');
                }
                break;

            case CONFIRM_SIGNUP:
                if (User_Action::saveNewUser($user, false)) {
                    if(User_Action::confirmEmail($user)) {
                        $content[] = dgettext('users', 'User created successfully. Check your email for your login information.');
                    } else {
                        $result = $user->kill();
                        PHPWS_Error::logIfError($result);
                        $content[] = dgettext('users', 'There was problem creating your acccount. Check back later.');
                    }
                } else {
                    $content[] = dgettext('users', 'There was problem creating your acccount. Check back later.');
                }
        }

        return implode('<br />', $content);
    }

    public function confirmEmail($user)
    {
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $authkey = User_Action::_createSignupConfirmation($user->id);
        if (!$authkey) {
            return false;
        }

        $message = User_Action::_getSignupMessage($authkey);

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        $mail->addSendTo($user->email);
        $mail->setSubject(dgettext('users', 'Confirmation email'));
        $mail->setFrom($site_contact);
        $mail->setMessageBody($message);

        return $mail->send();
    }

    public function _getSignupMessage($authkey)
    {
        $http = PHPWS_Core::getHomeHttp();

        $template['LINK'] = sprintf('%sindex.php?module=users&action=user&command=confirm_user&hash=%s',
        $http, $authkey);

        $template['HOURS'] = NEW_SIGNUP_WINDOW;
        $template['SITE_NAME'] = Layout::getPageTitle(true);

        return PHPWS_Template::process($template, 'users', 'confirm/confirm.en-us.tpl');
    }

    public function _createSignupConfirmation($user_id)
    {
        $deadline = time() + (3600 * NEW_SIGNUP_WINDOW);
        $authkey = md5($deadline . $user_id);

        $db = new PHPWS_DB('users_signup');
        $db->addValue('authkey', $authkey);
        $db->addValue('user_id', $user_id);
        $db->addValue('deadline', $deadline);
        $result = $db->insert();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            return $authkey;
        }
    }

    public function saveNewUser(PHPWS_User $user, $approved)
    {
        $user->setPassword($user->_password);
        $user->setApproved($approved);
        $result = $user->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } elseif ($approved) {
            $user->login();
            $_SESSION['User'] = $user;
            Current_User::getLogin();
        }
        return true;
    }

    public function postPermission()
    {
        PHPWS_Core::initModClass('users', 'Permission.php');

        extract($_POST);

        // Error here
        if (!isset($group_id)) {
            return false;
        }

        foreach ($module_permission as $mod_title=>$permission){
            $subpermission = isset($sub_permission[$mod_title]) ? $sub_permission[$mod_title] : null;
            Users_Permission::setPermissions($group_id, $mod_title, $permission, $subpermission);
        }
    }

    // Moved to Current User
    public function loginUser($username, $password)
    {
        return Current_User::loginUser($username, $password);
    }


    public function postGroup(PHPWS_Group $group, $showLikeGroups=false)
    {
        $result = $group->setName($_POST['groupname'], true);
        if (PHPWS_Error::isError($result))
        return $result;
        $group->setActive(true);
        return true;
    }

    // Moved ot Current User
    public function authorize($authorize, $username, $password)
    {
        return Current_User::authorize($authorize, $username, $password);
    }


    public function badLogin()
    {
        Layout::add(dgettext('users', 'Username and password refused.'));
    }

    public static function getGroups($mode=null)
    {
        if (isset($GLOBALS['User_Group_List'])) {
            return $GLOBALS['User_Group_List'];
        }

        PHPWS_Core::initModClass('users', 'Group.php');

        $db = new PHPWS_DB('users_groups');
        if ($mode == 'users') {
            $db->addWhere('user_id', 0, '>');
        }
        elseif ($mode == 'group') {
            $db->addWhere('user_id', 0);
        }

        $db->addOrder('name');
        $db->setIndexBy('id');
        $db->addColumn('id');
        $db->addColumn('name');

        $result = $db->select('col');
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $GLOBALS['User_Group_List'] = $result;
        return $result;
    }

    public static function update_settings()
    {
        $error = null;

        if (!Current_User::authorized('users', 'settings')) {
            Current_User::disallow();
            return;
        }

        if (!isset($_POST['site_contact'])) {
            $error = dgettext('users', 'You need to set a site contact address.');
        } elseif (!PHPWS_Text::isValidInput($_POST['site_contact'], 'email')) {
            $error = dgettext('users', 'Please enter a valid email address as a site contact.');
        }

        $settings['site_contact'] = $_POST['site_contact'];

        if (Current_User::isDeity()) {

            if (is_numeric($_POST['user_signup'])) {
                $settings['new_user_method'] = (int)$_POST['user_signup'];
            }

            if (isset($_POST['hide_login'])) {
                $settings['hide_login'] = 1;
            } else {
                $settings['hide_login'] = 0;
            }

            if (isset($_POST['allow_remember'])) {
                $settings['allow_remember'] = 1;
            } else {
                $settings['allow_remember'] = 0;
            }

            if (isset($_POST['graphic_confirm'])) {
                $settings['graphic_confirm'] = 1;
            } else {
                $settings['graphic_confirm'] = 0;
            }
            $settings['user_menu'] = $_POST['user_menu'];

            $settings['allow_new_users'] = (int)$_POST['allow_new_users'];
        }
        $settings['forbidden_usernames'] = str_replace(' ', "\n", strtolower(strip_tags($_POST['forbidden_usernames'])));

        PHPWS_Settings::set('users', $settings);
        if ($error) {
            return $error;
        } else {
            PHPWS_Settings::save('users');
            return true;
        }
    }

    public static function getAuthorizationList()
    {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->addOrder('display_name');
        $result = $db->select();

        if (PHPWS_Error::logIfError($result)){
            return null;
        }

        return $result;
    }

    public function postAuthorization()
    {

        if (isset($_POST['add_script'])){
            if (!isset($_POST['file_list'])) {
                return false;
            }

            $db = new PHPWS_DB('users_auth_scripts');
            $db->addWhere('filename', strip_tags($_POST['file_list']));
            $result = $db->select('one');

            if (PHPWS_Error::isError($result)) {
                return $result;
            } elseif (!empty($result)) {
                return false;
            }

            $db->resetWhere();
            $db->addValue('display_name', $_POST['file_list']);
            $db->addValue('filename', $_POST['file_list']);
            $result = $db->insert();
            if (PHPWS_Error::isError($result)) {
                return $result;
            }
        } else {
            if (isset($_POST['default_authorization'])) {
                PHPWS_Settings::set('users', 'default_authorization', (int)$_POST['default_authorization']);
                PHPWS_Settings::save('users');
            }

            if (!empty($_POST['default_group'])) {
                $db = new PHPWS_DB('users_auth_scripts');
                foreach ($_POST['default_group'] as $auth_id => $group_id) {
                    $db->reset();
                    $db->addWhere('id', $auth_id);
                    $db->addValue('default_group', $group_id);
                    PHPWS_Error::logIfError($db->update());
                }
            }
        }
        return true;
    }

    public function dropAuthorization($script_id)
    {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->addWhere('id', (int)$script_id);
        $result = $db->delete();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
        $db2 = new PHPWS_DB('users');
        $db2->addWhere('authorize', $script_id);
        $db2->addValue('authorize', PHPWS_Settings::get('users', 'local_script'));
        return $db2->update();
    }

    public function postForgot(&$content)
    {
        if (empty($_POST['fg_username']) && empty($_POST['fg_email'])) {
            $content = dgettext('users', 'You must enter either a username or email address.');
            return false;
        }

        if (!empty($_POST['fg_username'])) {
            $username = $_POST['fg_username'];
            if (preg_match('/\'|"/', html_entity_decode(strip_tags($username), ENT_QUOTES))) {
                $content = dgettext('users', 'User name not found. Check your spelling or enter an email address instead.');
                return false;
            }

            $db = new PHPWS_DB('users');
            $db->addWhere('username', strtolower($username));
            $db->addColumn('email');
            $db->addColumn('id');
            $db->addColumn('deity');
            $db->addColumn('authorize');
            $user_search = $db->select('row');
            if (PHPWS_Error::logIfError($user_search)) {
                $content = dgettext('users', 'User name not found. Check your spelling or enter an email address instead.');
                return false;
            } elseif (empty($user_search)) {
                $content = dgettext('users', 'User name not found. Check your spelling or enter an email address instead.');
                return false;
            } else {
                if ($user_search['deity'] && !ALLOW_DEITY_FORGET) {
                    Security::log(dgettext('users', 'Forgotten password attempt made on a deity account.'));
                    $content = dgettext('users', 'User name not found. Check your spelling or enter an email address instead.');
                    return false;
                }

                if ($user_search['authorize'] != 1) {
                    $content = sprintf(dgettext('users', 'Sorry but your authorization is not checked on this site. Please contact %s for information on reseting your password.'),
                    PHPWS_User::getUserSetting('site_contact'));
                    return false;
                }

                if (PHPWS_Core::isPosted()) {
                    $content = dgettext('users', 'Please check your email for a response.');
                    return true;
                }

                if (empty($user_search['email'])) {
                    $content = dgettext('users', 'Your email address is missing from your account. Please contact the site administrators.');
                    PHPWS_Error::log(USER_ERR_NO_EMAIL, 'users', 'User_Action::postForgot');
                    return true;
                }

                if (User_Action::emailPasswordReset($user_search['id'], $user_search['email'])) {
                    $content = dgettext('users', 'We have sent you an email to reset your password.');
                    return true;
                } else {
                    $content = dgettext('users', 'We are currently unable to send out email reminders. Try again later.');
                    return true;
                }
            }

        } elseif (!empty($_POST['fg_email'])) {
            $email = $_POST['fg_email'];
            if (preg_match('/\'|"/', html_entity_decode(strip_tags($email), ENT_QUOTES))) {
                $content = dgettext('users', 'Email address not found. Please try again.');
                return false;
            }

            if (!PHPWS_Text::isValidInput($email, 'email')) {
                $content = dgettext('users', 'Email address not found. Please try again.');
                return false;
            }

            $db = new PHPWS_DB('users');
            $db->addWhere('email', $email);
            $db->addColumn('username');
            $user_search = $db->select('row');
            if (PHPWS_Error::logIfError($user_search)) {
                $content = dgettext('users', 'Email address not found. Please try again.');
                return false;
            } elseif (empty($user_search)) {
                $content = dgettext('users', 'Email address not found. Please try again.');
                return false;
            } else {
                if (PHPWS_Core::isPosted()) {
                    $content = dgettext('users', 'Please check your email for a response.');
                    return true;
                }

                if (User_Action::emailUsernameReminder($user_search['username'], $email)) {
                    $content = dgettext('users', 'We have sent you an user name reminder. Please check your email and return to log in.');
                    return true;
                } else {
                    $content = dgettext('users', 'We are currently unable to send out email reminders. Try again later.');
                    return true;
                }
            }
        }
    }

    public function emailPasswordReset($user_id, $email)
    {
        $db = new PHPWS_DB('users_pw_reset');

        // clear old reset rows
        $db->addWhere('timeout', time(), '<');
        PHPWS_Error::logIfError($db->delete());
        $db->reset();


        // check to see if they have already submitted a request
        $db->addWhere('user_id', (int)$user_id);
        $db->addColumn('user_id');
        $reset_present = $db->select('one');
        if (PHPWS_Error::logIfError($reset_present)) {
            return false;
        } elseif ($reset_present) {
            return true;
        }
        $db->reset();

        $page_title = $_SESSION['Layout_Settings']->getPageTitle(true);
        $url = PHPWS_Core::getHomeHttp();
        $hash = md5(time() .  $email);

        $message[] = dgettext('users', 'Did you forget your password at our site?');
        $message[] = dgettext('users', 'If so, you may click the link below to reset it.');
        $message[] = '';
        $message[] = sprintf('%sindex.php?module=users&action=user&command=rp&auth=%s',
        $url, $hash);
        $message[] = '';
        $message[] = dgettext('users', 'If you did not wish to reset your password, you may ignore this message.');
        $message[] = dgettext('users', 'You have one hour to respond.');

        $body = implode("\n", $message);

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        $mail->addSendTo($email);
        $mail->setSubject(dgettext('users', 'Forgot your password?'));
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $mail->setFrom(sprintf('%s<%s>', $page_title, $site_contact));
        $mail->setMessageBody($body);

        if ($mail->send()) {
            $db->addValue('user_id', $user_id);
            $db->addValue('authhash', $hash);
            // 1 hour limit = 3600
            $db->addValue('timeout', time() + 3600);
            if (PHPWS_Error::logIfError($db->insert())) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function emailUsernameReminder($username, $email)
    {
        $page_title = $_SESSION['Layout_Settings']->getPageTitle(true);
        $url = PHPWS_Core::getHomeHttp();
        $hash = md5(time() .  $email);

        $message[] = dgettext('users', 'Did you forget your user name at our site?');
        $message[] = sprintf(dgettext('users', 'The user name associated with your email address is "%s"'), $username);
        $message[] = '';
        $message[] = dgettext('users', 'Here is the address to return to our site:');
        $message[] = $url;
        $body = implode("\n", $message);

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        $mail->addSendTo($email);
        $mail->setSubject(dgettext('users', 'Forgot your user name?'));
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $mail->setFrom(sprintf('%s<%s>', $page_title, $site_contact));
        $mail->setMessageBody($body);

        return $mail->send();
    }

    /**
     * Returns user id is successful, zero otherwise
     */
    public function checkResetPassword()
    {
        @$auth = $_GET['auth'];
        if (empty($auth) || preg_match('/\W/', $auth)) {
            return 0;
        }

        $db = new PHPWS_DB('users_pw_reset');
        $db->addWhere('authhash', $auth);
        $db->addWhere('timeout', time(), '>');
        $db->addColumn('user_id');
        $result = $db->select('one');

        if (PHPWS_Error::logIfError($result)) {
            return false;
        } elseif (empty($result)) {
            return 0;
        } else {
            return $result;
        }
    }

    public function finishResetPW()
    {
        $result = PHPWS_User::checkPassword($_POST['password1'], $_POST['password2']);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        @$auth = $_POST['authhash'];
        @$user_id = (int)$_POST['user_id'];
        if (empty($user_id) || empty($auth) || preg_match('/\W/', $auth)) {
            return 0;
        }

        $db = new PHPWS_DB('users_pw_reset');
        $db->addWhere('user_id', $user_id);
        $db->addWhere('authhash', $auth);
        $db->addWhere('timeout', time(), '>');
        $result = $db->select();
        $db->reset();
        $db->addWhere('user_id', $user_id);
        if (PHPWS_Error::logIfError($result)) {
            $db->delete();
            return 0;
        } elseif (empty($result)) {
            $db->delete();
            return 0;
        } else {
            $user = new PHPWS_User($user_id);
            $user->setPassword($_POST['password1']);
            $result = $user->save();
            if (PHPWS_Error::logIfError($result)) {
                return 0;
            }

            Current_User::loginUser($user->username, $_POST['password1']);
            unset($user);
            $db->delete();
            return 1;
        }

    }

    public function checkPermissionTables()
    {
        PHPWS_Core::initModClass('users', 'Permission.php');
        $db = new PHPWS_DB('modules');
        $db->addWhere('active', 1);
        $db->addColumn('title');
        $result = $db->select('col');

        foreach ($result as $mod_title) {
            $content[] = '<br />';
            $content[] = sprintf(dgettext('users', 'Checking %s module'), $mod_title);

            $result = Users_Permission::registerPermissions($mod_title, $content);
            if (!$result) {
                $content[] = dgettext('users', 'No permissions file found.');
                continue;
            }
        }

        return implode('<br />', $content);
    }

    public function activateUser($user_id, $value)
    {
        $db = new PHPWS_DB('users');
        $db->addWhere('id', (int)$user_id);
        $db->addWhere('deity', 0);
        $db->addValue('active', $value ? 1 : 0);
        if (!PHPWS_Error::logIfError($db->update())) {
            $db = new PHPWS_DB('users_groups');
            $db->addWhere('user_id', $user_id);
            $db->addValue('active',  $value ? 1 : 0);
            return PHPWS_Error::logIfError($db->update());
        }
    }

    public function testForbidden($user)
    {
        $forbidden = PHPWS_Settings::get('users', 'forbidden_usernames');
        if (empty($forbidden)) {
            return true;
        }

        $names = explode("\n", $forbidden);
        if (empty($names)) {
            return true;
        }
        foreach ($names as $bad_name) {
            if (empty($bad_name)) {
                continue;
            }
            $bad_name = preg_quote(trim($bad_name));
            if (preg_match("/$bad_name/i", $user->username)) {
                return false;
            }
        }

        return true;
    }

    public static function askNotify($user)
    {
        $content[] = dgettext('users', 'Do you wish to notify the new user?');
        $_SESSION['New_User']['user_id']  = $user->id;
        $_SESSION['New_User']['username'] = $user->username;
        $_SESSION['New_User']['password'] = $_POST['password1'];
        $_SESSION['New_User']['email']    = $user->email;

        $vars['action']  = 'admin';
        $vars['command'] = 'notify_user';
        $content[] = PHPWS_Text::secureLink(dgettext('users', 'Yes, send them an email'), 'users', $vars);

        $vars['command'] = 'do_not_notify';
        $content[] = PHPWS_Text::secureLink(dgettext('users', 'No, do not notify'), 'users', $vars);

        return implode('<br />', $content);
    }

    public function notifyUser()
    {
        PHPWS_Core::initCoreClass('Mail.php');
        setLanguage(DEFAULT_LANGUAGE);
        if (!isset($_SESSION['New_User'])) {
            return;
        }
        extract($_SESSION['New_User']);
        $page_title = Layout::getPageTitle(true);


        $body[] = sprintf(dgettext('users', '%s created an user account for you.'), $page_title);
        $body[] = dgettext('users', 'You may log-in using the following information:');
        $body[] = sprintf(dgettext('users', 'Site address: %s'), PHPWS_Core::getHomeHttp());
        $body[] = sprintf(dgettext('users', 'Username: %s'), $username);
        $body[] = sprintf(dgettext('users', 'Password: %s'), $password);
        $body[] = dgettext('users', 'Please change your password immediately after logging in.');

        $mail = new PHPWS_Mail;
        $mail->addSendTo($email);
        $mail->setSubject(sprintf(dgettext('users', '%s account created'), $page_title));
        $mail->setFrom(PHPWS_User::getUserSetting('site_contact'));
        $mail->setReplyTo(PHPWS_User::getUserSetting('site_contact'));
        $mail->setMessageBody(implode("\n\n", $body));
        $result = $mail->send();
        return $result;
    }

    public static function assignDefaultGroup(PHPWS_User $user)
    {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->addColumn('default_group');
        $db->addColumn('id');
        $db->setIndexBy('id');
        $scripts = $db->select('col');

        $default_group = $scripts[$user->authorize]['default_group'];

        $group = new PHPWS_Group($default_group);

        if (!$group->id) {
            return false;
        }

        $group->addMember($user->_user_group);
        $group->save();
        return true;
    }
}

?>