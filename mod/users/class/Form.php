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
    $template["LOGOUT"] = PHPWS_Text::moduleLink(Translate::get("Log Out"), "users", array("action[user]"=>"logout"));
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
    $form->add("action[user]", "hidden", "loginBox");
    $form->add("block_username", "text", $username);
    $form->setTag("block_username", "USERNAME_FORM");
    $form->add("block_password", "password");
    $form->setTag("block_password", "PASSWORD_FORM");
    $form->add("submit", "submit", Translate::get("Log In"));
    
    $template = $form->getTemplate(TRUE, TRUE, $template);

    return PHPWS_Template::process($template, "users", "forms/loginBox.tpl");
  }

  function adminPanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $tabs["new_user"] = array("title"=>"New Users", "link"=>"index.php?module=users&amp;action[admin]=new_user");
    $tabs["manage_users"] = array("title"=>"Manage Users", "link"=>"index.php?module=users&amp;action[admin]=manage_users");

    $panel = new PHPWS_Panel("users");
    $panel->quickSetTabs($tabs);
    $panel->setContent("something");
    PHPWS_Layout::add(PHPWS_ControlPanel::display($panel->display()));

  }

}

?>
