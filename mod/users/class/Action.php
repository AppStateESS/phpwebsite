<?php

class User_Action {

  function adminAction($command){
    $content = NULL;

    PHPWS_Core::initModClass("users", "Form.php");
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    if (!$_SESSION['User']->allow("users")){
      PHPWS_User::disallow();
      return;
    }

    switch ($command){
      /** Main switch for tabs **/
    case "main":
      if (isset($_REQUEST['tab']))
	User_Action::adminAction($_REQUEST['tab']);
      else {
	$panel = & new PHPWS_Panel("users");

	$currentTab = $panel->getCurrentTab();

	if(isset($currentTab))
	  User_Action::adminAction($currentTab);
	else
	  User_Action::adminAction(DEFAULT_USER_MENU);
      }
      return;
      break;

      /** Form cases **/

      /** User Forms **/
    case "new_user":
      $user = & new PHPWS_User;
      $content = User_Form::userForm($user);
      break;

    case "manage_users":
      $content = User_Form::manageUsers();
      break;

    case "demographics":
      $content = User_Form::demographics();
      break;

    case "editUser":
      $user = & new PHPWS_User($_REQUEST["user"]);
      $content = User_Form::userForm($user);
      break;      

      /** End User Forms **/

      /** Group Forms **/

    case "setUserPermissions":
      if (!$_SESSION['User']->allow("users", "edit_permissions")){
	PHPWS_User::disallow();
        return;
      }

      PHPWS_Core::initModClass("users", "Group.php");
      $user = & new PHPWS_User($_REQUEST['user']);
      $id = $user->getUserGroup();
      if (PEAR::isError($id)){
	PHPWS_Error::log($id);
	$content = _("A fatal error occurred. Please check your logs.");
	break;
      }

      $content = User_Form::setPermissions($id, "user");
      break;


    case "setGroupPermissions":
      if (!$_SESSION['User']->allow("users", "edit_permissions")){
	PHPWS_User::disallow();
        return;
      }

      PHPWS_Core::initModClass("users", "Group.php");

      $content = User_Form::setPermissions($_REQUEST['group'], "group");
      break;


    case "new_group":
      PHPWS_Core::initModClass("users", "Group.php");
      $group = & new PHPWS_Group;
      $content = User_Form::groupForm($group);
      break;

    case "manage_groups":
      PHPWS_Core::killSession("Last_Member_Search");
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
      $content = User_Form::settings();
      break;

      /** End Misc Forms **/

      /** Action cases **/
    case "deify":
      $user = & new PHPWS_User($_REQUEST["user"]);
      if (isset($_GET['authorize'])){
	if ($_GET['authorize'] == 1 && $_SESSION['User']->isDeity()){
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
	if ($_GET['authorize'] == 1 && $_SESSION['User']->isDeity()){
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
      $id = (isset($_REQUEST['userId']) ? (int)$_REQUEST['userId'] : NULL);

      $user = & new PHPWS_User($id);
      $result = User_Action::postUser($user);

      if (is_array($result)){
	foreach ($result as $error)
	  $messages[] = $error->getMessage();
	$content = User_Form::userForm($user, implode("<br />", $messages));
      }
      else {
	$result = $user->save();
	if (PEAR::isError($result)){
	  $content = User_Form::userForm($user, $result->getMessage());
	  break;
	}

	if (!isset($id)){
	  $message = _print(_("User <b>[var1]</b> created successfully"), array($user->getUsername()));
	  unset($user);
	  $user = & new PHPWS_User;
	  $content = User_Form::userForm($user, $message);
	} else {
	  $content = _print(_("User <b>[var1]</b> updated successfully"), array($user->getUsername())) . "<hr />";
	  unset($user);
	  $content .= User_Form::manageUsers();
	}
      }
      break;

    case "postPermission":
      PHPWS_Core::initModClass("users", "Group.php");
      $id = $_POST['group'];
      $group = & new PHPWS_Group($id);
      User_Action::postPermission($group);

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


    default:
      $content = "Unknown command";
      echo phpws_debug::testarray($_REQUEST);
      break;
    }

    User_Form::adminPanel($content);
  }

  function userAction($command){
    switch ($command){
    case "loginBox":
      if (!PHPWS_Core::isPosted()){
	if (!User_Action::loginUser($_POST['block_username'], $_POST['block_password']))
	  User_Action::badLogin();
      }
      break;
      
    case "logout":
      PHPWS_Core::killAllSessions();
      PHPWS_Core::home();
      break;
    }
  }

  function postPermission(&$group){
    PHPWS_Core::initModClass("users", "Permission.php");

    if (isset($_POST['update']))
      foreach ($_POST['update'] as $update => $nullIt);
    elseif (isset($_POST['update_all']))
      $update = "all";
    else
      exit("need error in postPermission");

    $permission = $_POST['permission'];
    if (isset($_POST['subpermission']))
      $subperm = $_POST['subpermission'];

    if ($update == "all"){
      foreach ($permission as $itemname => $status){
	Users_Permission::setPermissions($group->getId(), $itemname, $status, isset($subperm[$itemname]) ? $subperm[$itemname] : NULL);
      }
    } elseif (isset($subperm)) {
      if (isset($subperm[$update]))
	$subpermission = $subperm[$update];
      else
	$subpermission = NULL;
      Users_Permission::setPermissions($group->getId(), $update, $permission[$update], $subpermission);
    } else
      Users_Permission::setPermissions($group->getId(), $update, $permission[$update]);

    return TRUE;
  }


  function loginUser($username, $password){
    // Note assuming here we are using the one username database
    // ie case insensitive

    $registrationScript = "default.php";

    include PHPWS_SOURCE_DIR . "mod/users/scripts/login/" . $registrationScript;

    if (!isset($logged) or $logged !== TRUE)
      return FALSE;

    if (isset($ID)){
      $_SESSION['User'] = new PHPWS_User($ID);
      $_SESSION['User']->setLogged(TRUE);
      User_Action::updateLastLogged();
      PHPWS_User::getLogin();
      return TRUE;
    }
    
    return FALSE;
  }

  function updateLastLogged(){
    $db = & new PHPWS_DB("users");
    $db->addWhere("id", $_SESSION['User']->getId());
    $db->addValue("last_logged", mktime());
    return $db->update();
  }


  function getUserConfig($setting){
    static $settings;

    if (!isset($settings)){
      $DB = new PHPWS_DB("users_config");
      $settings = $DB->select("row");
    }

    if (PEAR::isError($settings)){
      PHPWS_Error::log($settings);
      return NULL;
    }

    return $settings[$setting];
  }

  function postUser(&$user){
    if ($user->isUser())
      $result = $user->setUsername($_POST['username'], FALSE);
    else
      $result = $user->setUsername($_POST['username'], TRUE);

    if (PEAR::isError($result))
      $error[] = $result;

    if (!$user->isUser() || (!empty($_POST['password1']) || !empty($_POST['password2']))){
      $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

      if (PEAR::isError($result))
	$error[] = $result;
      else
	$user->setPassword($_POST['password1']);
    }

    if (isset($_POST['demographic'])){
      PHPWS_Core::initModClass("users", "Demographics.php");
      $result = Demographics::post($user);
    }

    if (isset($error))
      return $error;
    else
      return TRUE;
  }

  function postGroup(&$group, $showLikeGroups=FALSE){
    $result = $group->setName($_POST['groupname'], TRUE);
    if (PEAR::isError($result))
      return $result;

    return TRUE;
  }

  function postMembers(){
    if (isset($_POST['member_join'])){
      foreach($_POST['member_join'] as $id => $nullit);
      $group->addMember($id);
    }
  }

  function badLogin(){
    Layout::add("Unable to find your account. Please try again.", "User_Main");
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

}

?>