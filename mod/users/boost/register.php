<?php

function users_register($module, &$content){
  PHPWS_Core::initModClass("users", "Permission.php");
  PHPWS_Core::initModClass("users", "My_Page.php");

  $result = Users_Permission::createPermissions($module);
  
  if (PEAR::isError($result)){
    if ($result->getCode() == USER_ERR_PERM_FILE){
      $content[] = _("Permissions file not found.");
      PHPWS_Boost::addLog("users", _("Permissions file not found."));
    }
    else {
      $content[] = _("Permissions table not created successfully.");
      PHPWS_Error::log($result);
      return FALSE;
    }
  } else
    $content[] = _("Permissions table created successfully.");

  $result = My_Page::registerMyPage($module);

  if (PEAR::isError($result)){
    PHPWS_Boost::addLog("users", _("A problem occurred when trying to register this module to My Page."));
    $content[] = _("A problem occurred when trying to register this module to My Page.");
    return FALSE;
  } elseif ($result != FALSE)
    $content[] = _("My Page registered to Users module.");

  return TRUE;
}

?>