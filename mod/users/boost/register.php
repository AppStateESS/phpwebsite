<?php

function users_register($module, &$content){
  PHPWS_Core::initModClass("users", "Permission.php");

  $content[] = _("Creating permissions table.");
  if (Users_Permission::createPermissions($module)){
    $content[] = _("Permissions table created successfully.");
    return TRUE;
  }
  else {
    $content[] = _("Permissions table not created successfully.");
    return FALSE;
  }

}

?>