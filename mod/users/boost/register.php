<?php

function users_register($module, &$content){
  PHPWS_Core::initModClass("users", "Permission.php");

  $content[] = _("Creating permissions table.");
  $result = Users_Permission::createPermissions($module);
  
  if (PEAR::isError($result)){
    if ($result->getCode() == USER_ERR_PERM_FILE){
      $content[] = _("Permissions file not found.");
      PHPWS_Boost::addLog("users", _("Permissions file not found."));
    }
    else {
      $content[] = _("Permissions table not created successfully.");
      PHPWS_Error::log($result);
    }
    return FALSE;
  } else {
    $content[] = _("Permissions table created successfully.");
    return TRUE;
  }
}

?>