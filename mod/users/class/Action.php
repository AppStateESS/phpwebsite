<?php

class User_Action {

  function adminAction($command){

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
	$content = User_Action::adminAction($_REQUEST['tab']);
      else {
	$panel = & new PHPWS_Panel("users");

	$currentTab = $panel->getCurrentTab();

	if(isset($currentTab))
	  $content = User_Action::adminAction($currentTab);
	else
	  $content = User_Action::adminAction(DEFAULT_USER_MENU);
      }
      break;

      /** Form cases **/
    case "new_user":
      $user = & new PHPWS_User;
      return User_Form::userForm($user);
      break;

    case "manage_users":
      return User_Form::managerUsers($user);
      break;

    case "demographics":
      return User_Form::demographics();
      break;


      /** Action cases **/

    case "postUser":
      $user = & new PHPWS_User();
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
	$message = _print(_("User <b>[var1]</b> created successfully"), array($user->getUsername()));
	unset($user);
	$user = & new PHPWS_User;
	$content = User_Form::userForm($user, $message);
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
      PHPWS_User::getLogin();
      return TRUE;
    }
    
    return FALSE;
  }


  function &getSetting($setting){
    static $settings;

    if (!isset($settings)){
      $DB = new PHPWS_DB("users_settings");
      $settings = $DB->select("row");
    }

    return $settings[$setting];
  }

  function postUser(&$user){
    $result = $user->setUsername($_POST['username'], TRUE);

    if (PEAR::isError($result))
      $error[] = $result;

    $result = $user->checkPassword($_POST['password1'], $_POST['password2']);

    if (PEAR::isError($result))
      $error[] = $result;
    else
      $user->setPassword($_POST['password1']);

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