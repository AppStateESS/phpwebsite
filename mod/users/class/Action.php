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
      

      /** Action cases **/

    case "postUser":
      $id = ($_REQUEST['userId'] ? (int)$_REQUEST['userId'] : NULL);

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

    case "setActiveDemographics":
      User_Form::setActiveDemographics();
      $content = User_Form::demographics("Demographics updated");
      break;

    default:
      $content = "Unknown command";
      User_Action::adminAction(DEFAULT_USER_MENU);
      break;
    }

    User_Form::adminPanel($content);
  }

  function userAction($command){
    switch ($command){
    case "loginBox":
      if (!PHPWS_Core::isLastPost()){
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


  function badLogin(){
    Layout::add("Unable to find your account. Please try again.", "User_Main");
  }

}

?>