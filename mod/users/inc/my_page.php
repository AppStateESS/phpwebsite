<?php

function my_page(){
  PHPWS_Core::initModClass('help', 'Help.php');
  if (isset($_REQUEST['subcommand']))
    $subcommand = $_REQUEST['subcommand'];
  else
    $subcommand = 'updateSettings';

  $user = $_SESSION['User'];
  $template['TITLE'] = _('Change my Settings');

  switch ($subcommand){
  case 'updateSettings':
    $content = User_Settings::userForm($user);
    break;

  case 'postUser':
    $result = User_Action::postUser($user, FALSE);
    if (is_array($result)) {
      $content = User_Settings::userForm($user, $result);
    }
    else {
      $user->save();
      $_SESSION['User'] = $user;
      $template['MESSAGE'] = _('User settings updated.');
      $content = User_Settings::userForm($user);
    }
    break;
  }

  $template['CONTENT'] = $content;

  return PHPWS_Template::process($template, 'users', 'my_page/main.tpl'); 
}

class User_Settings {

  function userForm(&$user, $message=NULL){
    translate('users');
    Layout::addStyle('users');

    $form = & new PHPWS_Form;

    $form->addHidden('module', 'users');
    $form->addHidden('action', 'user');
    $form->addHidden('command', 'my_page');
    $form->addHidden('subcommand', 'postUser');

    if (Current_User::allow('users') || $user->display_name == $user->username) {
      $form->addText('display_name', $user->display_name);
      $form->setLabel('display_name', _('Display Name'));
    } else {
      $form->addTplTag('DISPLAY_NAME_LABEL', _('Display Name'));
      $form->addTplTag('DISPLAY_NAME', PHPWS_Help::show_link('users', 'display_name_change', $user->display_name));
    }

    if ($user->canChangePassword()){
      $form->addPassword('password1');
      $form->addPassword('password2');
      $form->setTitle('password2', _('Password confirm'));
      $form->setLabel('password1', _('Password'));
    } else {
      $tpl['PASSWORD1_LABEL'] =  _('Password');
      $tpl['PASSWORD1'] = PHPWS_Help::show_link('users', 'no_password', _('Why can\'t I change my password?'));
    }

    $form->addText('email', $user->getEmail());
    $form->setSize('email', 40);
    $form->setLabel('email', _('Email Address'));

    if (isset($tpl))
      $form->mergeTemplate($tpl);

    $form->addHidden('userId', $user->getId());
    $form->addSubmit('submit', _('Update my information'));

    $template = $form->getTemplate();

    if (isset($message)){
      foreach ($message as $tag=>$error)
	$template[$tag] = $error;
    }

    return PHPWS_Template::process($template, 'users', 'my_page/user_setting.tpl');
  }
}

?>