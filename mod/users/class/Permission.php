<?php

class Users_Permission {
  var $permissions = NULL;
  var $groups      = NULL;
  
  function Users_Permission($groups=NULL){
    $this->groups = $groups;
  }


  function allow($module, $subpermission=NULL, $item_id=NULL, $returnType=FALSE){
    if (!isset($this->permissions[$module])){
      $result = Users_Permission::loadPermission($module, $this->permissions);
    }

    $permissionLvl = (int)$this->permissions[$module]['permission_level'];

    if (!$returnType && $permissionLvl == 0)
      return FALSE;
      

    if(isset($this->permissions[$module]['permissions'])){
      if (isset($subpermission)){
	if (!isset($this->permissions[$module]['permissions'][$subpermission])){
	  PHPWS_Error::log(USER_ERR_FAIL_ON_SUBPERM, "users", "allow", "SubPerm: $subpermission");
	  return FALSE;
	}
	$allow = $this->permissions[$module]['permissions'][$subpermission];

	if ((bool)$allow){
	  if (isset($item_id)){
	    if (in_array($item_id, $this->permissions[$module]['items']))
	      return ($returnType ? $permissionLvl : TRUE);
	    else
	      return FALSE;
	  }
	  else
	    return ($returnType ? $permissionLvl : TRUE);
	} else
	  return FALSE;
      } else
	return ($returnType ? $permissionLvl : TRUE);
    } else {
      return ($returnType ? $permissionLvl : (bool)$permissionLvl);
    }
  }

  function loadPermission($module, &$permissions){
    $groups = $this->groups;
    $permTable = Users_Permission::getPermissionTableName($module);
    $itemTable = Users_Permission::getItemPermissionTableName($module);

    PHPWS_DB::isTable($itemTable) ? $useItem = TRUE : $useItem = FALSE;

    if(!PHPWS_DB::isTable($permTable)){
      $permissions[$module]['permission_level'] = FULL_PERMISSION;
      return TRUE;
    }

    $permDB = new PHPWS_DB($permTable);
    $itemDB = new PHPWS_DB($itemTable);

    if (isset($groups) && count($groups)){
      foreach ($groups as $group_id){
	if ($useItem)
	  $itemDB->addWhere("group_id", $group_id, NULL, "or");
	
	$permDB->addWhere("group_id", $group_id, NULL, "or");
      }
    }

    $permResult = $permDB->select();

    if (!isset($permResult)){
      $permissions[$module]['permission_level'] = NO_PERMISSION;
      return TRUE;
    }

    if ($useItem){
      $itemResult = $itemDB->select("col");

      if (PEAR::isError($itemResult))
	return $itemResult;

      if (!isset($itemResult))
	$itemResult = array();
    } else
      $itemResult = NULL;

    $permissionSet = array();
    foreach ($permResult as $permission){
      unset($permission['group_id']);
      $permissionLevel = $permission['permission_level'];
      unset($permission['permission_level']);
      
      foreach($permission as $name=>$value){
	if (!isset($permissionSet[$name]))
	  $permissionSet[$name] = $value;
	elseif ($permissionSet[$name] < $value)
	  $permissionSet[$name] = $value;
      }
    }

    $permissions[$module]['permission_level'] = $permissionLevel;
    $permissions[$module]['items']            = $itemResult;
    $permissions[$module]['permissions']      = $permissionSet;

    return TRUE;
  }


  function createPermissions($module){
    $permissions = NULL;
    $file = PHPWS_Core::getConfigFile($module, "permission.php");
    if (PEAR::isError($file))
      return PHPWS_Error::get(USER_ERR_PERM_FILE, "users", "createPermissions", "Module: $module");

    include_once $file;

    $result = Users_Permission::createPermissionTable($module, $permissions);
    if (PEAR::isError($result))
      $errors[] = $result;
      
    if (isset($itemPermissions[$module]) && $itemPermissions[$module] == TRUE){
      $result = Users_Permission::createItemPermissionTable($module);
      if (PEAR::isError($result))
	$errors[] = $result;
    }

    if (isset($errors)){
      foreach ($errors as $error)
	PHPWS_Error::log($error);
      return FALSE;
    }

    return TRUE;
  }

  function createPermissionTable($module, $permissions=NULL){
    $tableName = Users_Permission::getPermissionTableName($module);
    $columnSetting = "smallint NOT NULL default '0'";

    if (PHPWS_DB::isTable($tableName))
      return PHPWS_Error::get(USER_ERR_PERM_TABLE, "users", "createPermissionTable", "Table Name: $tableName");

    $DB = new PHPWS_DB($tableName);
    
    $columns['group_id'] = "int NOT NULL default '0'";
    $columns['permission_level'] = "smallint NOT NULL default'0'";

    if (isset($permissions)){
      foreach ($permissions as $permission=>$description)
	$columns[$permission] = &$columnSetting;
    }

    $DB->addValue($columns);
    return $DB->createTable();
  }

  function createItemPermissionTable($module){
    $tableName = Users_Permission::getItemPermissionTableName($module);

    if (PHPWS_DB::isTable($tableName))
      return PHPWS_Error::get(USER_ERR_PERM_TABLE, "users", "createItemPermissionTable", "Table Name: $tableName");
    
    $DB = new PHPWS_DB($tableName);
    
    $columns['item_id'] = $columns['group_id'] = "int NOT NULL default '0'";
    $DB->addValue($columns);
    return $DB->createTable();
  }

  function getPermissionTableName($module){
    return implode("", array($module, "_permissions"));    
  }

  function getItemPermissionTableName($module){
    return implode("", array($module, "_item_permissions"));
  }


  function setPermissions($group_id, $module, $level, $subpermissions=NULL){
    $tableName = Users_Permission::getPermissionTableName($module);
    $db = new PHPWS_DB($tableName);
    $db->addWhere("group_id", $group_id);

    $db->delete();

    $db->resetWhere();

    $db->addValue("group_id", (int)$group_id);
    $columns = $db->getTableColumns();

    $db->addValue("permission_level", (int)$level);

    if (isset($subpermissions)){
      foreach ($columns as $colName){
	if ($colName == "permission_level" || $colName == "group_id")
	  continue;
	
	if (isset($subpermissions[$colName]) && (int)$subpermissions[$colName] == 1)
	  $db->addValue($colName, 1);
	else
	  $db->addValue($colName, 0);
      }
    }

    $db->insert();

  }
  

}


?>