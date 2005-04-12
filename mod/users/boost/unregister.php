<?php

function users_unregister($module, &$content){
  PHPWS_Core::initModClass('users', 'Permission.php');

  $content[] = _('Removing permissions table.');
  if (Users_Permission::removePermissions($module)){
    $content[] = _('Permissions table removed successfully.');
    return TRUE;
  }
  else {
    $content[] = _('Permissions table not removed successfully.');
    return FALSE;
  }
}


?>