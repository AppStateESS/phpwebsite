<?php

class User_Functions {

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

  function adminAction($command){
    PHPWS_Core::initModClass("users", "Form.php");
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    switch ($command){
    case "main":
      if (isset($_REQUEST['tab']))
	$content = PHPWS_User_Form::adminForm($_REQUEST['tab']);
      else {
	$panel = new PHPWS_Panel("users");
	$currentTab = $panel->getCurrentTab();
	if(isset($currentTab))
	  $content = PHPWS_User_Form::adminForm($currentTab);
	else
	  $content = PHPWS_User_Form::adminForm(DEFAULT_USER_MENU);
      }
      break;

    case "post_newUser":
      if (PHPWS_Core::isLastPost())
	$content =  PHPWS_User_Form::adminForm("new_user");
      
      $user = &PHPWS_User_Form::postUser();
      if (isset($user->message))
	$content =  PHPWS_User_Form::adminForm("new_user", $user);
      else {
	$user->save();
	unset($user);
	$content =  MSG_USER_CREATED;
      }
      break;

    case "setActiveDemographics":
      PHPWS_User_Form::setActiveDemographics();
      $content = PHPWS_User_Form::demographics("Demographics updated");
      break;

    default:
      $content =  "Unknown command";
      break;
    }

    PHPWS_User_Form::adminPanel($content);
  }

  function badLogin(){
    Layout::add("Unable to find your account. Please try again.", "User_Main");
  }

}
?>