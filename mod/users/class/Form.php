<?php

PHPWS_Core::initCoreClass("Form.php");

class PHPWS_User_Form {

  function logBox($logged=TRUE){
    if ($logged){
      $username = $_SESSION['User']->getUsername();
      $form['TITLE']   = Translate::get("Hello [var1]", $username);
      $form['CONTENT'] = PHPWS_User_Form::loggedIn();
    }
    else {
      $form['TITLE']   = Translate::get("Please Login");
      $form['CONTENT'] = PHPWS_User_Form::loggedOut();
    }

    return $form;
  }


  function loggedIn(){
    PHPWS_Core::initCoreClass("Text.php");
    $template["MODULES"] = PHPWS_Text::moduleLink(Translate::get("Control Panel"), "controlpanel");
    $template["LOGOUT"] = PHPWS_Text::moduleLink(Translate::get("Log Out"), "users", array("action[open]"=>"logout"));
    $template["HOME"] = PHPWS_Text::moduleLink(Translate::get("Home"));

    return PHPWS_Template::process($template, "users", "usermenus/Default.tpl");
  }

  function loggedOut(){
    if (isset($_REQUEST["block_username"]))
      $username = $_REQUEST["block_username"];
    else
      $username = NULL;

    $template["USERNAME"] = Translate::get("Username");
    $template["PASSWORD"] = Translate::get("Password");
    
    $form = new PHPWS_Form("User_Login");
    $form->add("module", "hidden", "users");
    $form->add("action[open]", "hidden", "loginBox");
    $form->add("block_username", "text", $username);
    $form->setTag("block_username", "USERNAME_FORM");
    $form->add("block_password", "password");
    $form->setTag("block_password", "PASSWORD_FORM");
    $form->add("submit", "submit", Translate::get("Log In"));
    
    $template = $form->getTemplate(TRUE, TRUE, $template);

    return PHPWS_Template::process($template, "users", "forms/loginBox.tpl");
  }
}

?>
