<?php

function new_account($item)
{
  if (!PHPWS_User::getUserSetting('new_user_method') > 0) {
    return _('New user signup is currently disabled.');
  }
  $signup_vars = array('action'  => 'user',
		       'command' => 'signup_user');
  
  return PHPWS_Text::moduleLink(USER_SIGNUP_QUESTION, 'users', $signup_vars);
}

?>