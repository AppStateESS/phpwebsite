<?php

class User_Manager extends PHPWS_User{

  function listActive(){
    if ($this->isActive())
      return _('Yes');
    else
      return _('No');
  }

  function listLastLogged(){
    $logged = $this->getLastLogged('%c');

    if (empty($logged))
      return _('Never');
    else
      return $logged;

  }

  function listAction($user){
    $id = $user->id;

    $linkVar['action'] = 'admin';
    $linkVar['user_id'] = $id;

    $jsvar['QUESTION'] = sprintf(_('Are you certain you want to delete the user &quot;%s&quot; permanently?'),
				 $user->getUsername());
    $jsvar['ADDRESS']  = 'index.php?module=users&amp;action=admin&amp;command=deleteUser&amp;user_id='
      . $id . '&amp;authkey=' . Current_User::getAuthKey();
    $jsvar['LINK']     = _('Delete');
    
    
    $linkVar['command'] = 'editUser';
    $links[] = PHPWS_Text::secureLink(_('Edit'), 'users', $linkVar);

    $linkVar['command'] = 'setUserPermissions';
    $links[] = PHPWS_Text::secureLink(_('Permissions'), 'users', $linkVar);

    if (!$user->isDeity() && ($user->id != Current_User::getId())) {
      $links[] = Layout::getJavascript('confirm', $jsvar);
    }

    if ($user->active){
      $linkVar['command'] = 'deactivateUser';
      $links[] = PHPWS_Text::secureLink(_('Deactivate'), 'users', $linkVar);
    } else {
      $linkVar['command'] = 'activateUser';
      $links[] = PHPWS_Text::secureLink(_('Activate'), 'users', $linkVar);
    }

    return implode(' | ', $links);
  }

  function listEmail(){
    return $this->getEmail(TRUE, TRUE);
  }
}

?>