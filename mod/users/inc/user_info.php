<?php

function my_page(){
  if (isset($_REQUEST['subcommand']))
    $subcommand = $_REQUEST['subcommand'];
  else
    $subcommand = "updateSettings";

  $user = $_SESSION['User'];

  switch ($subcommand){
  case "updateSettings":
    $content = User_Settings::userForm($user);
    break;

  case "postUser":
    $result = User_Action::postUser($user, FALSE);
    if (is_array($result))
      $content = User_Settings::userForm($user, $result);
      
    break;
  }

  return $content;
}

class User_Settings {

  function userForm(&$user, $message=NULL){
    translate("users");
    Layout::addStyle("users");

    $form = & new PHPWS_Form;

    $form->addHidden("module", "users");
    $form->addHidden("action", "user");
    $form->addHidden("command", "my_page");
    $form->addHidden("subcommand", "postUser");

    $form->addPassword("password1");
    $form->addPassword("password2");
    $form->addText("email", $user->getEmail());

    $form->setLabel("email", _("Email Address"));
    $form->setLabel("password1", _("Password"));

    if (isset($tpl))
      $form->mergeTemplate($tpl);

    $form->addHidden("userId", $user->getId());
    $form->addSubmit("submit", _("Update my information"));

    $template = $form->getTemplate();
    if (isset($message)){
      foreach ($message as $tag=>$error)
	$template[$tag] = $error;
    }

    return PHPWS_Template::process($template, "users", "forms/user_setting.tpl");
  }
}

?>