<?php

function users_install(&$content, $branchInstall=FALSE){
  PHPWS_Core::initModClass("users", "Users.php");
  PHPWS_Core::initModClass("users", "Action.php");
  PHPWS_Core::initModClass("users", "Demographics.php");
  include PHPWS_Core::getConfigFile("users", "config.php");

  $user = & new PHPWS_User;
  $content[] = "<hr />";

  if ($branchInstall==FALSE){
    if (isset($_POST['module']) && $_POST['module']=="users"){
      $result = User_Action::postUser($user);

      if (!is_array($result)){
	$anon = new PHPWS_User;
	$anon->setUsername("ANON");
	$anon->setEmail("blank@127.0.0.1");
	$anon->setActive(TRUE);
	$anon->setApproved(TRUE);
	$anon->setAuthorize(0);
	$result = $anon->save();

	if (PEAR::isError($result))
	  return $result;

	$user->setDeity(TRUE);
	$user->setActive(TRUE);
	$user->setApproved(TRUE);
	$user->setAuthorize(1);
	$result = $user->save();
	if (PEAR::isError($result))
	  return $result;

	$content[] = _("User created successfully.");
	$db = & new PHPWS_DB("users_auth_scripts");
	$db->addValue("display_name", _("Local"));
	$db->addValue("filename", "local.php");
	$db->insert();
      } else {
	$content[] = userForm($user, $result);
	return FALSE;
      }
    } else {
      $content[] = _("Please create a user to administrate the site.") . "<br />";
      $content[] = userForm($user);
      return FALSE;
    }
  }

  return TRUE;
}


function userForm(&$user, $errors=NULL){
  PHPWS_Core::initCoreClass("Form.php");
  PHPWS_Core::initModClass("users", "Form.php");

  translate("users");
  $form = & new PHPWS_Form;

  $form->addHidden("step", 3);
  $form->addHidden("module", "users");
  $form->addText("username", $user->getUsername());
  $form->addText("email", $user->getEmail());
  $form->addPassword("password1");
  $form->addPassword("password2");

  $form->setLabel("username", _("Username"));
  $form->setLabel("password1", _("Password"));
  $form->setLabel("email", _("Email"));

  $form->addSubmit("submit", _("Add User"));
  
  $form->mergeTemplate($tpl);

  $template = $form->getTemplate();

  if (!empty($errors))
    foreach ($errors as $tag=>$message)
      $template[$tag] = $message;

  $result = PHPWS_Template::process($template, "users", "forms/userForm.tpl");

  $content[] = $result;
  return implode("\n", $content);
}


?>