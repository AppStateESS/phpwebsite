<?php

class User_Manager extends PHPWS_User{

  function listActive(){
    if ($this->isActive())
      return _("Yes");
    else
      return _("No");
  }

  function listLastLogged(){
    $logged = $this->getLastLogged("%c");

    if (empty($logged))
      return _("Never");
    else
      return $logged;

  }

  function listAction($user){
    $id = $user->id;

    $linkVar['action'] = "admin";
    $linkVar['user_id'] = $id;

    $linkVar['command'] = "editUser";
    $links[] = PHPWS_Text::secureLink(_("Edit"), "users", $linkVar);

    $linkVar['command'] = "setUserPermissions";
    $links[] = PHPWS_Text::secureLink(_("Permissions"), "users", $linkVar);

    if ($user->active){
      $linkVar['command'] = "deactivateUser";
      $links[] = PHPWS_Text::secureLink(_("Deactivate"), "users", $linkVar);
    } else {
      $linkVar['command'] = "activateUser";
      $links[] = PHPWS_Text::secureLink(_("Activate"), "users", $linkVar);
    }

    return implode(" | ", $links);
  }

}

?>