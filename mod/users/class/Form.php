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

  function adminPanel($content){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    if ($_SESSION['User']->allow("users", "add_edit_users")){
      $tabs["new_user"] = array("title"=>"New User", "link"=>"index.php?module=users&amp;action[admin]=main");
      $tabs["manage_users"] = array("title"=>"Manage Users", "link"=>"index.php?module=users&amp;action[admin]=main");
    }

    if ($_SESSION['User']->allow("users", "add_edit_groups")){
      $tabs["new_group"] = array("title"=>"New Group", "link"=>"index.php?module=users&amp;action[admin]=main");
      $tabs["manage_groups"] = array("title"=>"Manage Groups", "link"=>"index.php?module=users&amp;action[admin]=main");
    }


    $panel = new PHPWS_Panel("users");
    $panel->quickSetTabs($tabs);
    $panel->setContent($content);
    PHPWS_Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }

  function newUser($user=NULL){
    $form = &PHPWS_User_Form::userForm($user);
    $form->add("submit", "submit", "Add User");

    $template = $form->getTemplate();
    if (isset($user->message)){
      $template['MESSAGE'] = implode("<br />", $user->message);
      $user->message = NULL;
    }

    $result = PHPWS_Template::process($template, "users", "forms/userForm.tpl");
    return $result;
  }

  function managerUsers(){
    PHPWS_Core::initCoreClass("Manager.php");
    PHPWS_Core::initModClass("users", "Manager.php");
    $manager = new PHPWS_User_Manager;
    $content = $manager->getList("users", "Testing User Title");
    return $content;
  }

  function &userForm($user=NULL){
    if (!isset($user))
      $user = new PHPWS_User;

    $form = new PHPWS_Form("new_user");
    $form->add("module", "hidden", "users");
    $form->add("action[admin]", "hidden", "post_newUser");
    $form->add("username", "text", $user->getUsername());
    $form->add("password1", "password");
    $form->add("password2", "password");

    return $form;
  }

  function &postUser(){
    $user = new PHPWS_User();

    $result = $user->setUsername($_POST['username'], TRUE);
    if (PEAR::isError($result))
      $user->message[] = $result->getMessage();

    $result = $user->checkPassword($_POST['password1'], $_POST['password2']);
    if (PEAR::isError($result))
      $user->message[] = $result->getMessage();
    else
      $user->setPassword($_POST['password1']);

    return $user;
  }

  function adminForm($command, $user=NULL){

    switch ($command){
    case "new_user":
      return PHPWS_User_Form::newUser($user);
      break;

    case "manage_users":
      return PHPWS_User_Form::managerUsers($user);
      break;

    }

  }

}

?>