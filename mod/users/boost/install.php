<?php

function install(&$content){
  PHPWS_Core::initModClass("users", "Users.php");
  PHPWS_Core::initModClass("users", "Action.php");
  include PHPWS_Core::getConfigFile("users", "config.php");

  $user = & new PHPWS_User;
  $content[] = "<hr />";

  if (isset($_POST['module']) && $_POST['module']=="users"){
    $result = User_Action::postUser($user);
    if (!is_array($result)){
      $anon = new PHPWS_User;
      $anon->setUsername(_("Anonymous"));
      $result = $anon->save();

      if (PEAR::isError($result))
	return $result;

      $user->setDeity(TRUE);
      $result = $user->save();
      if (PEAR::isError($result))
	return $result;

      $content[] = _("User created successfully.");

      $db = & new PHPWS_DB("users_config");
      $db->addValue("anonymous", $anon->getId());

      return TRUE;
    } else {
      foreach ($result as $error)
	$errors[] = $error->getMessage();
      $content[] = userForm($user, implode("<br />", $errors));
      return FALSE;
    }
  } else {
    $content[] = _("Please create a user to administrate the site.") . "<br />";
    $content[] = userForm($user);
    return FALSE;
  }

}


function userForm(&$user, $message=NULL){
  PHPWS_Core::initCoreClass("Form.php");
  PHPWS_Core::initModClass("users", "Form.php");

  translate("users");
  $form = & new PHPWS_Form;
  $form->add("submit", "submit", _("Add User"));

  if (isset($message))
    $tpl['MESSAGE'] = $message;

  $tpl['USERNAME_LBL'] = _("Username");
  $tpl['PASSWORD_LBL'] = _("Password");

  $form->add("step", "hidden", 3);
  $form->add("module", "hidden", "users");
  $form->add("username", "text", $user->getUsername());
  $form->add("password1", "password");
  $form->add("password2", "password");
  
  $form->mergeTemplate($tpl);

  $template = $form->getTemplate();

  $result = PHPWS_Template::process($template, "users", "forms/userForm.tpl");

  $content[] = $result;
  return implode("\n", $content);
}


?>