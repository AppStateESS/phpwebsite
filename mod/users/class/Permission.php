<?php

define("DEFAULT_PERMISSION_ITEMNAME", "common");

class PHPWS_User_Permission{

  function createPermissions($module){
    include "mod/$module/conf/permission.php";
    if (!isset($permissions))
      return PEAR::raiseError(PHPWS_User::errorMessage(USER_ERR_PERM_FILE) . ": <b>$module</b>", USER_ERR_PERM_FILE);

    foreach ($permissions as $itemName=>$subpermissions){
      $result = PHPWS_User_Permission::createPermissionTable($module, $itemName, $subpermissions);
      if (PEAR::isError($result))
	$errors[] = $result;

      if (isset($itemPermissions[$itemName]) && $itemPermissions[$itemName]==TRUE){
	$result = PHPWS_User_Permission::createItemPermissionTable($module, $itemName);
	if (PEAR::isError($result))
	  $errors[] = $result;
      }
    }

    if (isset($errors))
      echo phpws_debug::testarray($errors);

  }

  function createPermissionTable($module, $itemName, $subpermissions=NULL){
    $tableName = PHPWS_User_Permission::getPermissionTableName($module, $itemName);
    $columnSetting = "smallint NOT NULL default '0'";

    if (PHPWS_DB::isTable($tableName))
      return PEAR::raiseError(PHPWS_User::errorMessage(USER_ERR_PERM_TABLE) . ": <b>$tableName</b>", USER_ERR_PERM_TABLE);

    $DB = new PHPWS_DB($tableName);
    
    $columns['group_id'] = "int NOT NULL default '0'";

    if (isset($subpermissions)){
      foreach ($subpermissions as $permission=>$description)
	$columns[$permission] = &$columnSetting;
    }

    $DB->addValue($columns);
    return $DB->createTable();
  }

  function createItemPermissionTable($module, $itemName){
    $tableName = PHPWS_User_Permission::getItemPermissionTableName($module, $itemName);

    if (PHPWS_DB::isTable($tableName))
      return PEAR::raiseError(PHPWS_User::errorMessage(USER_ERR_PERM_TABLE) . ": <b>$tableName</b>", USER_ERR_PERM_TABLE);
    
    $DB = new PHPWS_DB($tableName);
    
    $columns['item_id'] = $columns['group_id'] = "int NOT NULL default '0'";
    $DB->addValue($columns);
    return $DB->createTable();
  }

  function getPermissionTableName($module, $itemName){
    return implode("", array($module, "_", $itemName, "_permissions"));    
  }

  function getItemPermissionTableName($module, $itemName){
    return implode("", array($module, "_", $itemName, "_item_permissions"));
  }


  function setPermissions($group_id, $module, $itemName, $permissions){
    $tableName = PHPWS_User_Permission::getPermissionTableName($module, $itemName);
    $DB = new PHPWS_DB($tableName);
    $DB->addWhere("group_id", $group_id);
    $newRights = $DB->select("row");

    if (PEAR::isError($newRights))
      return $newRights;

    if (isset($newRights)){
      foreach ($permissions as $name=>$switch)
	$newRights[$name] = $switch;
      $command = "update";
    } else {
      $newRights = $permissions;
      $newRights['group_id'] = $group_id;
      $command = "insert";
    }
    echo phpws_debug::testarray($newRights);
    echo phpws_debug::testobject($DB);
    $DB->addValue($newRights);
    $DB->$command();
    echo $DB->lastQuery();
  }
  

}


?>