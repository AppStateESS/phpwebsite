<?php
/**
 * Controls results from forms and administration functions
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */


class User_Action {

  function adminAction(){
    $content = NULL;

    if (!Current_User::allow("users")){
      PHPWS_User::disallow(_("User tried to perform an Users admin function."));
      return;
    }

    $panel = & User_Action::cpanel();
    
    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = $panel->getCurrentTab();

    if (isset($_REQUEST['user_id']))
      $user = & new PHPWS_User($_REQUEST['user_id']);
    else
      $user = & new PHPWS_User;

    switch ($command){
      /** Form cases **/
      /** User Forms **/
    case "new_user":
      $title = _("Create User");
      $content = User_Form::userForm($user);
      break;

    case "manage_users":
      $title = _("Manage Users");
      $content = User_Form::manageUsers();
      break;

    case "demographics":
      $content = User_Form::demographics();
      break;

    case "editUser":
      $title = _("Edit User");
      $user = & new PHPWS_User($_REQUEST["user_id"]);
      $content = User_Form::userForm($user);
      break;      

    case "authorization":
      $title = _("Authorization");
      $content = User_Form::authorizationSetup();
      break;

    case "dropAuthScript":
      if (isset($_REQUEST['script_id']))
	User_Action::dropAuthorization($_REQUEST['script_id']);
      $content = User_Form::authorizationSetup();
      break;

    case "postAuthorization":
      User_Action::postAuthorization();
      $content = _("Authorization updated.") . "<hr />" . User_Form::authorizationSetup();
      break;

      /** End User Forms **/

      /** Group Forms **/

    case "setUserPermissions":
      if (!Current_User::allow("users", "edit_permissions")){
	PHPWS_User::disallow();
        return;
      }

      PHPWS_Core::initModClass("users", "Group.php");
      $user = & new PHPWS_User($_REQUEST['user_id']);
      $title = sprintf(_("Permissions for %s"), $user->getUsername());
      $content = User_Form::setPermissions($user->getUserGroup());
      break;


    case "setGroupPermissions":
      if (!Current_User::allow("users", "edit_permissions")){
	PHPWS_User::disallow();
        return;
      }

      PHPWS_Core::initModClass("users", "Group.php");

      $content = User_Form::setPermissions($_REQUEST['group'], "group");
      break;


    case "new_group":
      PHPWS_Core::initModClass("users", "Group.php");
      $group = & new PHPWS_Group;
      $title = _("Create Group");
      $content = User_Form::groupForm($group);
      break;

    case "manage_groups":
      PHPWS_Core::killSession("Last_Member_Search");
      $title = _("Manage Groups");
      $content = User_Form::manageGroups();
      break;

    case "manageMembers":
      PHPWS_Core::initModClass("users", "Group.php");
      $group = & new PHPWS_Group($_REQUEST['group']);
      $content = User_Form::manageMembers($group);
      break;

      /** End Group Forms **/

      /** Misc Forms **/
    case "settings":
      $title = _("Settings");
      $content = User_Form::settings();
      break;

      /** End Misc Forms **/

      /** Action cases **/
    case "deify":
      $user = & new PHPWS_User($_REQUEST["user"]);
      if (isset($_GET['authorize'])){
	if ($_GET['authorize'] == 1 && Current_User::isDeity()){
	  $user->setDeity(TRUE);
	  $user->save();
	  $content = _("User deified.") . "<hr />" . User_Form::manageUsers();
	  break;
	} else {
	  $content = _("User remains a lowly mortal.") . "<hr />" . User_Form::manageUsers();
	  break;
	}
      } else
	$content = User_Form::deify($user);
      break;      

    case "mortalize":
      $user = & new PHPWS_User($_REQUEST["user"]);
      if (isset($_GET['authorize'])){
	if ($_GET['authorize'] == 1 && Current_User::isDeity()){
	  $user->setDeity(FALSE);
	  $user->save();
	  $content = _("User transformed into a lowly mortal.") . "<hr />" . User_Form::manageUsers();
	  break;
	} else {
	  $content = _("User remains a deity.") . "<hr />" . User_Form::manageUsers();
	  break;
	}
      } else 
	$content = User_Form::mortalize($user);
      break;      

    case "postUser":
      if (PHPWS_Core::isPosted()){
	$content = _("This is a duplicate post.");
	break;
      }
      $result = User_Action::postUser($user);
      break;

    case "postPermission":
      User_Action::postPermission();
      echo "posting permissions";
      break;
      $content = _("Permissions updated.") . "<hr />";

      if ($_POST['type'] == "user")
	$content .= User_Form::manageUsers();
      else
	$content .= User_Form::manageGroups();

      break;

    case "postGroup":
      PHPWS_Core::initModClass("users", "Group.php");
      $id = (isset($_REQUEST['groupId']) ? (int)$_REQUEST['groupId'] : NULL);

      $group = & new PHPWS_Group($id);
      $result = User_Action::postGroup($group);

      if (PEAR::isError($result)){
	$content = $result->getMessage() . "<hr />";
	$content .= User_form::groupForm($group);
      } else {
	$result = $group->save();
	if (PEAR::isError($result)){
	  PHPWS_Error::log($result);
	  $content .= _("An error occurred when trying to save the group.") . "<hr />";
	} else
	  $content .= _("Group created.") . "<hr />";
	$group = & new PHPWS_Group($id);
	$content .= User_form::groupForm($group);
      }
      break;

    case "setActiveDemographics":
      User_Form::setActiveDemographics();
      $content = User_Form::demographics("Demographics updated");
      break;

    case "addMember":
      PHPWS_Core::initModClass("users", "Group.php");
      $group = & new PHPWS_Group($_REQUEST['group']);
      $group->addMember($_REQUEST['member']);
      $group->save();
      $content = User_Form::manageMembers($group);
      break;

    case "dropMember":
      PHPWS_Core::initModClass("users", "Group.php");
      $group = & new PHPWS_Group($_REQUEST['group']);
      $group->dropMember($_REQUEST['member']);
      $group->save();
      $content = User_Form::manageMembers($group);
      break;

    case "update_settings":
      $result = User_Action::update_settings();
      $content = _("User settings updated.") . "<hr />";
      $content .= User_Form::settings();
      break;

    default:
      $content = "Unknown command";
      test($_REQUEST);
      break;
    }

    $template['CONTENT'] = $content;
    $template['TITLE'] = $title;

    $final = PHPWS_Template::process($template, "users", "main.tpl");

    $panel->setContent($final);

    Layout::add(PHPWS_ControlPanel::display($panel->display()));

  }

  function postUser(&$user, $set_username=TRUE){
    if ($set_username){
      $result = $user->setUsername($_POST['username']);
      if (PEAR::isError($result))
	$error['USERNAME_ERROR'] = $result->getMessage();
    }

    if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
      $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

      if (PEAR::isError($result))
	$error['PASSWORD_ERROR'] = $result->getMessage();
      else
	$user->setPassword($_POST['password1']);
    }

    $result = $user->setEmail($_POST['email']);
    if (PEAR::isError($result))
      $error['EMAIL_ERROR'] = $result->getMessage();

    if (isset($error))
      return $error;
    else
      return TRUE;
  }

  function &cpanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $link = "index.php?module=users&amp;action=admin";

    $tabs["new_user"] = array("title"=>_("New User"), "link"=>$link);
    
    if (Current_User::allow("users", "edit_users") || Current_User::allow("users", "delete_users"))
      $tabs["manage_users"] = array("title"=>_("Manage Users"), "link"=>$link);

    if (Current_User::allow("users", "add_edit_groups")){
      $tabs["new_group"] = array("title"=>_("New Group"), "link"=>$link);
      $tabs["manage_groups"] = array("title"=>_("Manage Groups"), "link"=>$link);
    }

    $tabs['authorization'] = array("title"=>_("Authorization"), "link"=>$link);

    if (Current_User::allow("users", "settings"))
      $tabs["settings"] = array("title"=>_("Settings"), "link"=>$link);

    $panel = & new PHPWS_Panel("user_user_panel");
    $panel->quickSetTabs($tabs);
    $panel->setModule("users");
    $panel->setPanel("panel.tpl");
    return $panel;
  }


  function userAction(){
    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = "my_page";

    switch ($command){
    case "loginBox":
      if (!Current_User::isLogged()){
	if (!User_Action::loginUser($_POST['block_username'], $_POST['block_password']))
	  User_Action::badLogin();
	else
	  Current_User::getLogin();
      }
      break;
      
    case "my_page":
      PHPWS_Core::initModClass("users", "My_Page.php");
      $my_page = & new My_Page;
      $my_page->main();
      break;

    case "logout":
      PHPWS_Core::killAllSessions();
      PHPWS_Core::home();
      break;
    }
  }

  function postPermission(){
    PHPWS_Core::initModClass("users", "Permission.php");

    extract($_POST);
    
    // Error here
    if (!isset($group_id))
      return FALSE;

    foreach ($module_permission as $mod_title=>$permission){
      $subpermission = isset($sub_permission[$mod_title]) ? $sub_permission[$mod_title] : NULL;
      Users_Permission::setPermissions($group_id, $mod_title, $permission, $subpermission);
    }
  }


  function loginUser($username, $password){
    $createUser = FALSE;
    // First check if they are currently a user in local system
    $user = & new PHPWS_User;

    $db = & new PHPWS_DB("users");
    $db->addWhere("username", strtolower(preg_replace("/\W/", "", $username)));
    $result = $db->loadObject($user);

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    }

    // if result is blank then check against the default authorization
    if ($result == FALSE){
      $authorize = PHPWS_User::getUserSetting("default_authorization");
      $createUser = TRUE;
    }
    else
      $authorize = $user->getAuthorize();

    if (empty($authorize))
      return FALSE;

    $result = User_Action::authorize($authorize, $username, $password);

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    }

    if ($result == TRUE){
      if ($createUser == TRUE){
	$result = $user->setUsername($username);

	if (PEAR::isError($result)){
	  PHPWS_Error::log($result);
	  return FALSE;
	}

	$user->setAuthorize($authorize);
	$user->setActive(TRUE);
	$user->setApproved(TRUE);

	if (function_exists("post_authorize"))
	  post_authorize($user);

	$user->save();
      }

      $user->setLogged(TRUE);
      $user->setLastLogged(mktime());
      $user->addLogCount();
      $user->makeAuthKey($password);
      $user->save();
      $_SESSION['User'] = $user;
      return TRUE;
    } else
      return FALSE;
  }


  function postGroup(&$group, $showLikeGroups=FALSE){
    $result = $group->setName($_POST['groupname'], TRUE);
    if (PEAR::isError($result))
      return $result;

    return TRUE;
  }

  function authorize($authorize, $username, $password){
    $db = & new PHPWS_DB("users_auth_scripts");
    $db->setIndexBy("id");
    $result = $db->select();

    if (empty($result))
      return FALSE;

    if (isset($result[$authorize])){
      extract($result[$authorize]);
      $file = "mod/users/scripts/$filename";
      if(!is_file($file)){
	PHPWS_Error::log(USER_ERR_MISSING_AUTH, "users", "authorize", $file);
	return FALSE;
      }

      include $file;
      if (function_exists("authorize")){
	$result = authorize($username, $password);
	return $result;
      } else {
	PHPWS_Error::log(USER_ERR_MISSING_AUTH, "users", "authorize");
	return FALSE;
      }
    } else
      return FALSE;

    return $result;
  }


  function postMembers(){
    if (isset($_POST['member_join'])){
      foreach($_POST['member_join'] as $id => $nullit);
      $group->addMember($id);
    }
  }

  function badLogin(){
    Layout::add(_("Username and password refused."), "users", "User_Main");
  }

  function getGroups($mode=NULL){
    PHPWS_Core::initModClass("users", "Group.php");

    $db = & new PHPWS_DB("users_groups");
    if ($mode == "users")
      $db->addWhere("user_id", 0, ">");
    elseif ($mode == "group")
      $db->addWhere("user_id", 0);

    $db->addOrder("name");
    $db->setIndexBy("id");
    $db->addColumn("name");

    return $db->select("col");
  }

  function update_settings(){
    $db = & new PHPWS_DB("users_config");
    if (is_numeric($_POST['default_group']))
      $db->addValue("default_group", $_POST['default_group']);
    
    if (is_numeric($_POST['default_authorization']))
      $db->addValue("default_authorization", $_POST['default_authorization']);

    $db->update();
  }

  function getAuthorizationList(){
    $db = & new PHPWS_DB("users_auth_scripts");
    $db->addOrder("display_name");
    $result = $db->select();

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return NULL;
    }

    return $result;
  }

  function postAuthorization(){
    if (isset($_POST['add_script'])){
      if (!isset($_POST['file_list']))
	return FALSE;

      $db = & new PHPWS_DB("users_auth_scripts");
      $db->addWhere("filename", $_POST['file_list']);
      $result = $db->select("one");
      if (PEAR::isError($result))
	return $result;
      elseif (!empty($result))
	return;

      $db->resetWhere();
      $db->addValue("display_name", $_POST['file_list']);
      $db->addValue("filename", $_POST['file_list']);
      $result = $db->insert();
      if (PEAR::isError($result))
	return $result;
    }


    if (isset($_POST['default_authorization'])){
      $db = & new PHPWS_DB("users_config");
      $db->addValue("default_authorization", (int)$_POST['default_authorization']);
      $result = $db->update();
      if (PEAR::isError($result))
	return $result;
    }
    return TRUE;
  }

  function dropAuthorization($script_id){
    $db = & new PHPWS_DB("users_auth_scripts");
    $db->addWhere("id", (int)$script_id);
    return $db->delete();
  }

}

?>