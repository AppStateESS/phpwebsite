<?php

class PHPWS_User_Permission{

  function createPermissions($module){
    // FIX
    include "mod/$module/conf/permission.php";
    if (!isset($permissions))
      return PHPWS_Error::get(USER_ERR_PERM_FILE, "users", "createPermissions", "Module: $module");

    foreach ($permissions as $itemName=>$subpermissions){
      $result = PHPWS_User_Permission::createPermissionTable($itemName, $subpermissions);
      if (PEAR::isError($result))
	$errors[] = $result;

      if (isset($itemPermissions[$itemName]) && $itemPermissions[$itemName]==TRUE){
	$result = PHPWS_User_Permission::createItemPermissionTable($itemName);
	if (PEAR::isError($result))
	  $errors[] = $result;
      }
    }

    if (isset($errors)){
      echo phpws_debug::testarray($errors);
      echo "createPermissions Permission.php";
    }
  }

  function createPermissionTable($itemName, $subpermissions=NULL){
    $tableName = PHPWS_User_Permission::getPermissionTableName($itemName);
    $columnSetting = "smallint NOT NULL default '0'";

    if (PHPWS_DB::isTable($tableName))
      return PHPWS_Error::get(USER_ERR_PERM_TABLE, "users", "createPermissionTable", "Table Name: $tableName");

    $DB = new PHPWS_DB($tableName);
    
    $columns['group_id'] = "int NOT NULL default '0'";

    if (isset($subpermissions)){
      foreach ($subpermissions as $permission=>$description)
	$columns[$permission] = &$columnSetting;
    }

    $DB->addValue($columns);
    return $DB->createTable();
  }

  function createItemPermissionTable($itemName){
    $tableName = PHPWS_User_Permission::getItemPermissionTableName($itemName);

    if (PHPWS_DB::isTable($tableName))
      return PHPWS_Error::get(USER_ERR_PERM_TABLE, "users", "createItemPermissionTable", "Table Name: $tableName");
    
    $DB = new PHPWS_DB($tableName);
    
    $columns['item_id'] = $columns['group_id'] = "int NOT NULL default '0'";
    $DB->addValue($columns);
    return $DB->createTable();
  }

  function getPermissionTableName($itemName){
    return implode("", array($itemName, "_permissions"));    
  }

  function getItemPermissionTableName($itemName){
    return implode("", array($itemName, "_item_permissions"));
  }


  function setPermissions($group_id, $itemName, $permissions){
    $tableName = PHPWS_User_Permission::getPermissionTableName($itemName);
    $DB = new PHPWS_DB($tableName);
    $DB->addWhere("group_id", $group_id);
    $newRights = $DB->select("row");

    if (PEAR::isError($newRights))
      return $newRights;

    if (isset($newRights)){
      foreach ($permissions as $name=>$switch)
	$newRights[$name] = (int)$switch;
      $command = "update";
    } else {
      $newRights = $permissions;
      $newRights['group_id'] = $group_id;
      $command = "insert";
    }
    $DB->addValue($newRights);
    $DB->$command();

  }
  

}


?>