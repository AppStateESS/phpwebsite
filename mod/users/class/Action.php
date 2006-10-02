<?php
/**
 * Controls results from forms and administration functions
 *
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';

class User_Action {

    function adminAction()
    {
        PHPWS_Core::initModClass('users', 'Group.php');
        $message = $content = NULL;
        
        if (!Current_User::allow('users')) {
            PHPWS_User::disallow(_('Tried to perform an admin function in Users.'));
            return;
        }

        $message = User_Action::getMessage();
        $panel = & User_Action::cpanel();
        $panel->enableSecure();
    
        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        if (isset($_REQUEST['user_id'])) {
            $user = & new PHPWS_User((int)$_REQUEST['user_id']);
        } else {
            $user = & new PHPWS_User;
        }

        if (isset($_REQUEST['group_id'])) {
            $group = & new PHPWS_Group((int)$_REQUEST['group_id']);
        } else {
            $group = & new PHPWS_Group;
        }

        switch ($command) {
            /** Form cases **/
            /** User Forms **/
        case 'new_user':
            $panel->setCurrentTab('new_user');
            $title = _('Create User');
            $content = User_Form::userForm($user);
            break;

        case 'manage_users':
            $title = _('Manage Users');
            $content = User_Form::manageUsers();
            break;

        case 'demographics':
            $content = User_Form::demographics();
            break;

        case 'editUser':
            $title = _('Edit User');
            $user = & new PHPWS_User($_REQUEST['user_id']);
            $content = User_Form::userForm($user);
            break;      

        case 'deleteUser':
            if (!Current_User::authorized('users', 'delete_users')) {
                Current_User::disallow();
                return;
            }
            $user->kill();
            $title = _('Manage Users');
            $content = User_Form::manageUsers();
            $message = _('User deleted.');
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

            if (!Current_User::authorized('users', 'settings')) {
                Current_User::disallow();
                return;
            }

            if ($command == 'dropAuthScript' && isset($_REQUEST['script_id'])) {
                User_Action::dropAuthorization($_REQUEST['script_id']);
            } elseif ($command == 'postAuthorization') {
                User_Action::postAuthorization();
                $message = _('Authorization updated.');
            }
            $title = _('Authorization');
            $content = User_Form::authorizationSetup();
            break;

        case 'editScript':
            $title = _('Edit Authorization Script');
            // no reason to edit scripts yet
            break;

        case 'setUserPermissions':
            if (!Current_User::authorized('users', 'edit_permissions')){
                PHPWS_User::disallow();
                return;
            }

            PHPWS_Core::initModClass('users', 'Group.php');
            $title = _('Set User Permissions') . ' : ' . $user->getUsername();
            $content = User_Form::setPermissions($user->getUserGroup());
            break;

            /** End User Forms **/

            /********************** Group Forms ************************/

        case 'setGroupPermissions':
            if (!Current_User::authorized('users', 'edit_permissions')){
                PHPWS_User::disallow();
                return;
            }

            PHPWS_Core::initModClass('users', 'Group.php');
            $title = _('Set Group Permissions') .' : '. $group->getName();
            $content = User_Form::setPermissions($_REQUEST['group_id'], 'group');
            break;


        case 'new_group':
            $title = _('Create Group');
            $content = User_Form::groupForm($group);
            break;

        case 'edit_group':
            $title = _('Edit Group');
            $content = User_Form::groupForm($group);
            break;

        case 'remove_group':
            $group->kill();
            $title = _('Manage Groups');
            $content = User_Form::manageGroups();
            break;

        case 'manage_groups':
            $panel->setCurrentTab('manage_groups');
            PHPWS_Core::killSession('Last_Member_Search');
            $title = _('Manage Groups');
            $content = User_Form::manageGroups();
            break;

        case 'manageMembers':
            PHPWS_Core::initModClass('users', 'Group.php');
            $title = _('Manage Members') . ' : ' . $group->getName();
            $content = User_Form::manageMembers($group);
            break;

        case 'postMembers':
            if (!Current_User::authorized('users', 'add_edit_groups')) {
                Current_User::disallow();
                return;
            }

            $title = _('Manage Members') . ' : ' . $group->getName();
            $content = User_Form::manageMembers($group);
            break;

            /************************* End Group Forms *******************/

            /************************* Misc Forms ************************/
        case 'settings':
            if (!Current_User::authorized('users', 'settings')) {
                Current_User::disallow();
                return;
            }

            $title = _('Settings');
            $content = User_Form::settings();
            break;

            /** End Misc Forms **/

            /** Action cases **/
        case 'deify':
            if (!Current_User::isDeity()) {
                Current_User::disallow();
                return;
            }
            $user = & new PHPWS_User($_REQUEST['user']);
            if (isset($_GET['authorize'])){
                if ($_GET['authorize'] == 1 && Current_User::isDeity()){
                    $user->setDeity(TRUE);
                    $user->save();
                    User_Action::sendMessage(_('User deified.'), 'manage_users');
                    break;
                } else {
                    User_Action::sendMessage(_('User remains a lowly mortal.'), 'manage_users');
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

            $user = & new PHPWS_User($_REQUEST['user']);
            if (isset($_GET['authorize'])){
                if ($_GET['authorize'] == 1 && Current_User::isDeity()){
                    $user->setDeity(FALSE);
                    $user->save();
                    $content = _('User transformed into a lowly mortal.') . '<hr />' . User_Form::manageUsers();
                    break;
                } else {
                    $content = _('User remains a deity.') . '<hr />' . User_Form::manageUsers();
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

            if ($result === TRUE){
                $user->setActive(TRUE);
                $user->setApproved(TRUE);
                $user->save();
                $panel->setCurrentTab('manage_users');

                if (isset($_POST['user_id'])) {
                    User_Action::sendMessage(_('User updated.'), 'manage_users');
                } elseif (Current_User::allow('users', 'edit_permissions')) {
                    User_Action::sendMessage(_('User created.'), 'setUserPermissions&user_id=' . $user->id);
                } else {
                    User_Action::sendMessage(_('User created.'), 'new_user');
                }
            } else {
                $message = implode('<br />', $result);
                if (isset($_POST['user_id'])) {
                    $title = _('Edit User');
                }
                else {
                    $title = _('Create User');
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
            User_Action::sendMessage(_('Permissions updated'), $panel->getCurrentTab());
            break;

        case 'postGroup':
            if (!Current_User::authorized('users', 'add_edit_groups')) {
                PHPWS_User::disallow();
                return;
            }

            PHPWS_Core::initModClass('users', 'Group.php');
            $result = User_Action::postGroup($group);

            if (PEAR::isError($result)){
                $message = $result->getMessage();
                $title = isset($group->id) ? _('Edit Group') : _('Create Group');
                $content = User_form::groupForm($group);
            } else {
                $result = $group->save();

                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $message = _('An error occurred when trying to save the group.');
                } else {
                    $message = _('Group created.');
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
            User_Action::sendMessage(_('Member added.'), 'manageMembers&group_id=' . $group->id);
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
            User_Action::sendMessage(_('Member removed.'), 'manageMembers&group_id=' . $group->id);
            break;

        case 'update_settings':
            if (!Current_User::authorized('users', 'settings')) {
                PHPWS_User::disallow();
                return;
            }
            $title = _('Settings');

            $result = User_Action::update_settings();
            if ($result === TRUE) {
                $message = _('User settings updated.');
            } else {
                $message = $result;
            }
                $content = User_Form::settings();
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

    function popupPermission()
    {

        if (!isset($_REQUEST['key_id'])) {
            PHPWS_Core::goBack();
        }

        $key = & new Key((int)$_REQUEST['key_id']);

        if (!Key::checkKey($key, FALSE)) {
            PHPWS_Core::errorPage();
            return;
        }

        if (Current_User::isRestricted($key->module) ||
            !$key->allowEdit()) {
            Current_User::disallow();
        }

        $content = User_Action::getPermissionForm($key);
        Layout::nakedDisplay($content);
    }

    function getPermissionForm(&$key)
    {
        if (Current_User::isUnrestricted($key->module) && 
            Current_User::allow($key->module, $key->edit_permission)) {
            $tpl = User_Form::permissionMenu($key, TRUE);

            return PHPWS_Template::process($tpl, 'users', 'forms/permission_pop.tpl');
        }
    }


    function permission()
    {
        if (!isset($_REQUEST['key_id'])) {
            return;
        }

        $key = & new Key((int)$_REQUEST['key_id']);

        if (!Key::checkKey($key, FALSE)) {
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

        $result = $key->save();

        if (isset($_POST['popbox'])) {
            $tpl['TITLE'] = _('Permissions saved.');
            $tpl['BUTTON'] = sprintf('<input type="button" name="close_window" value="%s" onclick="window.close()" />', _('Close window'));
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'users', 'close.tpl'));
        } else {
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $_SESSION['Permission_Message'] = _('An error occurred.');
            } else {
                $_SESSION['Permission_Message'] = _('Permissions updated.');
            }

            PHPWS_Core::goBack();
        }
    }


    function getMessage()
    {
        if (!isset($_SESSION['User_Admin_Message'])) {
            return NULL;
        }
        $message = $_SESSION['User_Admin_Message'];
        unset($_SESSION['User_Admin_Message']);
        return $message;
    }

    function sendMessage($message, $command)
    {
        $_SESSION['User_Admin_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=users&action=admin&command='
                            . $command . '&authkey=' . Current_User::getAuthKey());
    }

    /**
     * Checks a new user's form for errors
     */
    function postNewUser(&$user)
    {
        $new_user_method = PHPWS_User::getUserSetting('new_user_method');

        $result = $user->setUsername($_POST['username']);
        if (PEAR::isError($result)) {
            $error['USERNAME_ERROR'] = _('Please try another user name.');
        }


        if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
            $result = $user->checkPassword($_POST['password1'], $_POST['password2']);
            
            if (PEAR::isError($result)) {
                $error['PASSWORD_ERROR'] = $result->getMessage();
            }
            else {
                $user->setPassword($_POST['password1'], FALSE);
            }
        }

        if (empty($_POST['email'])) {
            $error['EMAIL_ERROR'] = _('Missing an email address.');
        } else {
            $result = $user->setEmail($_POST['email']);
            if (PEAR::isError($result)) {
                $error['EMAIL_ERROR'] = _('This email address cannot be used.');
            }
        }

        if (!User_Action::confirm()) {
            $error['CONFIRM_ERROR'] = _('Confirmation phrase is not correct.');
        }

        if (isset($error)) {
            return $error;
        } else {
            return TRUE;
        }
    }


    function confirm()
    {
        if (!PHPWS_User::getUserSetting('graphic_confirm')) {
            return TRUE;
        }

        if (isset($_POST['confirm_graphic']) &&
            isset($_SESSION['USER_CONFIRM_PHRASE']) &&
            $_POST['confirm_graphic'] == $_SESSION['USER_CONFIRM_PHRASE']) {
            $result = TRUE;
        } else {
            $result = FALSE;
        }

        unset($_SESSION['USER_CONFIRM_PHRASE']);
        return $result;

    }

    function postUser(&$user, $set_username=TRUE)
    {
        if ($set_username){
            $user->_prev_username = $user->username;
            $result = $user->setUsername($_POST['username']);
            if (PEAR::isError($result)) {
                $error['USERNAME_ERROR'] = $result->getMessage();
            }

            if ( ($user->_prev_username != $user->username) && 
                 (empty($_POST['password1']) || empty($_POST['password2']))) {
                $error['PASSWORD_ERROR'] = _('Passwords must be reentered on user name change.');
            }
        }

        if (isset($_POST['display_name'])) {
            $result = $user->setDisplayName($_POST['display_name']);
            if (PEAR::isError($result)) {
                $error['DISPLAY_ERROR'] = $result->getMessage();
            }
        }

        if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
            $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

            if (PEAR::isError($result)) {
                $error['PASSWORD_ERROR'] = $result->getMessage();
            }
            else {
                $user->setPassword($_POST['password1']);
            }
        }

        $result = $user->setEmail($_POST['email']);
        if (PEAR::isError($result)) {
            $error['EMAIL_ERROR'] = $result->getMessage();
        }
    
        if (Current_User::isLogged() &&
            Current_User::allow('users', 'settings') &&
            isset($_POST['authorize'])) {
            $user->setAuthorize($_POST['authorize']);
        }

        if (isset($error)) {
            return $error;
        }
        else {
            return TRUE;
        }
    }

    function &cpanel()
    {
        translate('users');
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=users&amp;action=admin';

        $tabs['new_user'] = array('title'=>_('New User'), 'link'=>$link);
    
        if (Current_User::allow('users', 'edit_users') || Current_User::allow('users', 'delete_users'))
            $tabs['manage_users'] = array('title'=>_('Manage Users'), 'link'=>$link);

        if (Current_User::allow('users', 'add_edit_groups')){
            $tabs['new_group'] = array('title'=>_('New Group'), 'link'=>$link);
            $tabs['manage_groups'] = array('title'=>_('Manage Groups'), 'link'=>$link);
        }

        if (Current_User::allow('users', 'settings')) {
            $tabs['authorization'] = array('title'=>_('Authorization'), 'link'=>$link);
            $tabs['settings'] = array('title'=>_('Settings'), 'link'=>$link);
        }

        $panel = & new PHPWS_Panel('user_user_panel');
        $panel->quickSetTabs($tabs);
        $panel->setModule('users');
        $panel->setPanel('panel.tpl');
        return $panel;
    }

    /**
     * Controller of user requests. Based on the command request variable
     * defaults to my_page 
     */
    function userAction()
    {
        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = 'my_page';
        }

        switch ($command) {
        case 'login':
            if (!Current_User::isLogged()) {
                $result = User_Action::loginUser($_POST['phpws_username'], $_POST['phpws_password']);
                if (!$result) {
                    $title = _('Login page');
                    $message = _('Username and password combination not found.');
                    $content = User_Form::loginPage();
                } elseif(PEAR::isError($result)) {
                    if (preg_match('/L\d/', $result->code)) {
                        $title = _('Sorry');
                        $content = $result->getMessage();
                    } else {
                        PHPWS_Error::log($result);
                        $message = _('A problem occurred when accessing user information. Please try again later.');
                    }
                } else {
                    Current_User::getLogin();
                    PHPWS_Core::goBack();
                }
            }
            break;

        case 'my_page':
            PHPWS_Core::initModClass('users', 'My_Page.php');
            $my_page = & new My_Page;
            $my_page->main();
            break;

        case 'signup_user':
            $title = _('New Account Sign-up');
            if (Current_User::isLogged()) {
                $content = _('You already have an account.');
                break;
            }
            $user = & new PHPWS_User;
            if (PHPWS_User::getUserSetting('new_user_method') == 0) {
                $content = _('Sorry, we are not accepting new users at this time.');
                break;
            }
            $content = User_Form::signup_form($user);
            break;

        case 'submit_new_user':
            $title = _('New Account Sign-up');
            $user_method = PHPWS_User::getUserSetting('new_user_method');
            if ($user_method == 0) {
                Current_User::disallow(_('New user signup not allowed.'));
                return;
            }

            $user = & new PHPWS_User;
            $result = User_Action::postNewUser($user);

            if (is_array($result)) {
                $content = User_Form::signup_form($user, $result);
            } else {
                $content = User_Action::successfulSignup($user);
            }
            break;

        case 'logout':
            PHPWS_Core::killAllSessions();
            PHPWS_Core::home();
            break;

        case 'login_page':
            if (Current_User::isLogged()) {
                PHPWS_Core::home();
            }
            $title = _('Login Page');
            $content = User_Form::loginPage();
            break;

        case 'confirm_user':
            if (Current_User::isLogged()) {
                PHPWS_Core::home();
            }
            if (User_Action::confirmUser()) {
                $title = _('Welcome!');
                $content = _('Your account has been successfully activated. Please log in.');
            } else {
                $title = _('Sorry');
                $content = _('This authentication does not exist.<br />
 If you did not log in within the time frame specified in your email, please apply for another account.');
            }
            User_Action::cleanUpConfirm();
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

    function confirmUser()
    {
        $hash = $_GET['hash'];
        if (preg_match('/\W/', $hash)) {
            PHPWS_Core::errorPage('400');
            Security::log(sprintf(_('User tried to send bad hash (%s) to confirm user.'), $hash));
        }
        $db = & new PHPWS_DB('users_signup');
        $db->addWhere('authkey', $hash);
        $row = $db->select('row');

        if (PEAR::isError($row)) {
            PHPWS_Error::log($row);
            return FALSE;
        } elseif (empty($row)) {
            return FALSE;
        } else {
            $user_id = &$row['user_id'];
            $user = & new PHPWS_User($user_id);

            // If the deadline has not yet passed, approve the user, save, and return true
            if ($row['deadline'] > mktime()) {
                $db->delete();
                $user->approved = 1;
                $user->save();
                return TRUE;
            } else {
                // If the deadline has passed, delete the user and return false.
                $user->delete();
                return FALSE;
            }
        }

    }

    function cleanUpConfirm()
    {
        $db = & new PHPWS_DB('users_signup');
        $db->addWhere('deadline', mktime(), '<');
        $result = $db->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    function successfulSignup($user)
    {
        switch (PHPWS_User::getUserSetting('new_user_method')) {
        case AUTO_SIGNUP:
            $result = User_Action::saveNewUser($user, TRUE);
            if ($result) {
                $content[] = _('Account created successfully!');
                $content[] = _('You will return to the home page in five seconds.');
                $content[] = PHPWS_Text::moduleLink(_('Click here if you are not redirected.'));
                Layout::metaRoute();
            } else {
                $content[] = _('An error occurred when trying to create your account. Please try again later.');
            }
            break;

        case CONFIRM_SIGNUP:
            if (User_Action::saveNewUser($user, FALSE)) {
                if(User_Action::confirmEmail($user)) {
                    $content[] = _('User created successfully. Check your email for your login information.');
                } else {
                    $result = $user->kill();
                    if (PEAR::isError($result)) {
                        PHPWS_Error::log($result);
                    }
                    $content[] = _('There was problem creating your acccount. Check back later.');
                }
            } else {
                $content[] = _('There was problem creating your acccount. Check back later.');
            }
        }

        return implode('<br />', $content);

    }

    function confirmEmail($user)
    {
        $site_contact = PHPWS_User::getUserSetting('site_contact');
        $authkey = User_Action::_createSignupConfirmation($user->id);
        if (!$authkey) {
            return FALSE;
        }

        $message = User_Action::_getSignupMessage($authkey);

        PHPWS_Core::initCoreClass('Mail.php');
        $mail = & new PHPWS_Mail;
        $mail->addSendTo($user->email);
        $mail->setSubject(_('Confirmation email'));
        $mail->setFrom($site_contact);
        $mail->setMessageBody($message);

        return $mail->send();
    }

    function _getSignupMessage($authkey)
    {
        $http = PHPWS_Core::getHomeHttp();

        $template['LINK'] = sprintf('%sindex.php?module=users&action=user&command=confirm_user&hash=%s',
                                    $http, $authkey);

        $template['HOURS'] = NEW_SIGNUP_WINDOW;
        $template['SITE_NAME'] = Layout::getPageTitle(TRUE);

        return PHPWS_Template::process($template, 'users', 'confirm/confirm.en-us.tpl');
    }

    function _createSignupConfirmation($user_id)
    {
        $deadline = mktime() + (3600 * NEW_SIGNUP_WINDOW);
        $authkey = md5($deadline . $user_id);

        $db = & new PHPWS_DB('users_signup');
        $db->addValue('authkey', $authkey);
        $db->addValue('user_id', $user_id);
        $db->addValue('deadline', $deadline);
        $result = $db->insert();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        } else {
            return $authkey;
        }
    }

    function saveNewUser(&$user, $approved)
    {
        $user->setPassword($user->_password);
        $user->setApproved($approved);
        $result = $user->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return FALSE;
        } elseif ($approved) {
            $user->login();
            $_SESSION['User'] = $user;
            Current_User::getLogin();
        }
        return TRUE;
    }

    function postPermission()
    {
        PHPWS_Core::initModClass('users', 'Permission.php');

        extract($_POST);
    
        // Error here
        if (!isset($group_id)) {
            return FALSE;
        }

        foreach ($module_permission as $mod_title=>$permission){
            $subpermission = isset($sub_permission[$mod_title]) ? $sub_permission[$mod_title] : NULL;
            Users_Permission::setPermissions($group_id, $mod_title, $permission, $subpermission);
        }
    }

    // Moved to Current User
    function loginUser($username, $password)
    {
        return Current_User::loginUser($username, $password);
    }


    function postGroup(&$group, $showLikeGroups=FALSE)
    {
        $result = $group->setName($_POST['groupname'], TRUE);
        if (PEAR::isError($result))
            return $result;
        $group->setActive(TRUE);
        return TRUE;
    }

    // Moved ot Current User
    function authorize($authorize, $username, $password)
    {
        return Current_User::authorize($authorize, $username, $password);
    }


    function badLogin()
    {
        
        Layout::add(_('Username and password refused.'), 'users', 'User_Main');
    }

    function getGroups($mode=NULL, $group_list)
    {
        if (isset($GLOBALS['User_Group_List'])) {
            return $GLOBALS['User_Group_List'];
        }

        PHPWS_Core::initModClass('users', 'Group.php');

        $db = & new PHPWS_DB('users_groups');
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
        if (PEAR::isError($result)) {
            return $result;
        }

        $GLOBALS['User_Group_List'] = $result;
        return $result;
    }

    function update_settings()
    {
        $error = NULL;

        if (!Current_User::authorized('users', 'settings')) {
            Current_User::disallow();
            return;
        }

        $settings['site_contact'] = $_POST['site_contact'];
        if (!isset($_POST['site_contact'])) {
            $error = _('You need to set a site contact address.');
        } elseif (!PHPWS_Text::isValidInput($_POST['site_contact'], 'email')) {
            $error = _('Please enter a valid email address as a site contact.');
        }

        if (is_numeric($_POST['user_signup'])) {
            $settings['new_user_method'] = (int)$_POST['user_signup'];
        }

        if (isset($_POST['hide_login'])) {
            $settings['hide_login'] = 1;
        } else {
            $settings['hide_login'] = 0;
        }

        if (isset($_POST['graphic_confirm'])) {
            $settings['graphic_confirm'] = 1;
        } else {
            $settings['graphic_confirm'] = 0;
        }

        $settings['user_menu'] = $_POST['user_menu'];

        PHPWS_Settings::set('users', $settings);        
        if ($error) {
            return $error;
        } else {
            PHPWS_Settings::save('users');
            return TRUE;
        }
    }

    function getAuthorizationList()
    {
        $db = & new PHPWS_DB('users_auth_scripts');
        $db->addOrder('display_name');
        $result = $db->select();

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            return NULL;
        }

        return $result;
    }

    function postAuthorization()
    {
        if (isset($_POST['add_script'])){
            if (!isset($_POST['file_list'])) {
                return FALSE;
            }

            $db = & new PHPWS_DB('users_auth_scripts');
            $db->addWhere('filename', strip_tags($_POST['file_list']));
            $result = $db->select('one');

            if (PEAR::isError($result)) {
                return $result;
            } elseif (!empty($result)) {
                return FALSE;
            }

            $db->resetWhere();
            $db->addValue('display_name', $_POST['file_list']);
            $db->addValue('filename', $_POST['file_list']);
            $result = $db->insert();
            if (PEAR::isError($result)) {
                return $result;
            }
        }


        if (isset($_POST['default_authorization'])){
            $db = & new PHPWS_DB('users_config');
            $db->addValue('default_authorization', (int)$_POST['default_authorization']);
            $result = $db->update();

            if (PEAR::isError($result)) {
                return $result;
            }
            PHPWS_User::resetUserSettings();
        }
        return TRUE;
    }

    function dropAuthorization($script_id)
    {
        $db = & new PHPWS_DB('users_auth_scripts');
        $db->addWhere('id', (int)$script_id);
        return $db->delete();
    }

}

?>