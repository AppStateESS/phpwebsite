<?php

function install(&$content, $branchInstall=FALSE){
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
	$anon->setUsername(_("Anonymous"));
	$result = $anon->save();

	if (PEAR::isError($result))
	  return $result;

	$setting = & new PHPWS_DB("users_config");
	$setting->addValue("anonymous", $anon->getId());
	$setting->insert();

	$user->setDeity(TRUE);
	$result = $user->save();
	if (PEAR::isError($result))
	  return $result;

	$content[] = _("User created successfully.");

	$db = & new PHPWS_DB("users_config");
	$db->addValue("anonymous", $anon->getId());

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

  $content[] = _("Importing demographics information.");
  $result = Demographics::import("demographics.txt");
  if (PEAR::isError($result)){
    $content[] = _("And error occurred while importing your demographics settings.");
    $content[] = _("Please check your demographics.txt file.");
    PHPWS_Error::log($result);
  } else {
    $db = new PHPWS_DB("users_demographics");
    $db->addWhere("label", "contact_email");
    $db->addValue("active", 1);
    $db->update();
    $content[] = _("Import successful.");
  }
  return TRUE;

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