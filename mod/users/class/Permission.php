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
      if (PEAR::isError($result))
	return $result;
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
    $itemDB->addColumn("item_id");

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
     
      if (!isset($permissionLevel) || $permissionLevel < $permission['permission_level'])
	$permissionLevel = $permission['permission_level'];

      unset($permission['permission_level']);
      
      foreach($permission as $name=>$value){
	if (!isset($permissionSet[$name]) || $permissionSet[$name] < $value)
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

  function removePermissions($module){
    $tableName = Users_Permission::getPermissionTableName($module);
    $itemTableName = Users_Permission::getItemPermissionTableName($module);

    $DB = & new PHPWS_DB($tableName);

    $result = $DB->dropTable();
    if (PEAR::isError($result))
      $errors[] = $result;

    $result = $DB->setTable($itemTableName);
    if (PEAR::isError($result))
      $errors[] = $result;

    if (isset($errors)){
      foreach ($errors as $error)
	PHPWS_Error::log($error);
      return FALSE;
    }

    return TRUE;
  }

  function createPermissions($module){
    $permissions = NULL;
    $file = PHPWS_Core::getConfigFile($module, "permission.php");

    if ($file == FALSE)
      return NULL;

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

    $DB = & new PHPWS_DB($tableName);
    
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
    if (!PHPWS_DB::isTable($tableName))
      return;

    $db = new PHPWS_DB($tableName);
    $db->addWhere("group_id", (int)$group_id);

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

    return $db->insert();
  }

  function assignPermissions($module, $item_id=NULL){
    if ((int)Current_User::getPermissionLevel($module) < FULL_PERMISSION)
      return array('ASSIGNED_GROUPS_TITLE'=>NULL, 'ASSIGNED_GROUPS'=>NULL);

    $content = NULL;
    $groups = Users_Permission::_getPartial($module);

    if (PEAR::isError($groups)){
      PHPWS_Error::log($groups);
      $text['ASSIGNED_GROUPS_TITLE'] = _("Error");
      $text['ASSIGNED_GROUPS'] = _("An error occurred when accessing the permission system.");
    } elseif (empty($groups)){
      $text['ASSIGNED_GROUPS_TITLE'] = _("Assign Group Permissions");
      $text['ASSIGNED_GROUPS'] = _("No groups found.");
    } else
      $text = Users_Permission::_listAssigned($module, $groups, $item_id);

    if (PEAR::isError($text)){
      PHPWS_Error::log($text);
      unset($text);
      $text['ASSIGNED_GROUPS_TITLE'] = _("Error");
      $text['ASSIGNED_GROUPS'] = _("An error occurred when accessing the permission system.");
    }

    return $text;
  }

  function _getPartial($module){
    $itemTable = Users_Permission::getItemPermissionTableName($module);
    $permTable = Users_Permission::getPermissionTableName($module);

    if (!PHPWS_DB::isTable($permTable))
      return PHPWS_Error::get(USER_ERR_PERM_FILE, "users", __CLASS__ . "::" . __FUNCTION__);

    if (!PHPWS_DB::isTable($itemTable))
      return PHPWS_Error::get(USER_ERR_ITEM_PERM_FILE, "users", __CLASS__ . "::" . __FUNCTION__);

    $db = & new PHPWS_DB($permTable);
    $db->addWhere("permission_level", PARTIAL_PERMISSION);
    $db->addColumn("group_id");
    $result = $db->select("col");

    return $result;
  }

  function _listAssigned($module, $groups, $item_id=NULL){
    PHPWS_Core::initModClass("users", "Group.php");

    $db = & new PHPWS_DB("users_groups");
    foreach ($groups as $group_id)
      $db->addWhere("id", $group_id, "=", "OR");

    $result = $db->getObjects("PHPWS_Group");

    foreach ($result as $group)
      $inputs[$group->getId()] = $group->getName();

    $form = & new PHPWS_Form;
    $form->addMultiple("assigned_groups", $inputs);
    $form->setId("assigned_groups", "assigned_groups");
    $form->setLabel("assigned_groups", _("Assign Group Permissions"));
    $form->setWidth("assigned_groups", "200px");

    if (isset($item_id)){
      $itemTable = Users_Permission::getItemPermissionTableName($module);
      $db->reset();
      $db->setTable($itemTable);
      $db->addWhere("item_id", (int)$item_id);
      $db->addColumn ("group_id");
      $result = $db->select("col");

      if (PEAR::isError($result))
	return $result;

      if (!empty($result))
	$form->setMatch("assigned_groups", $result);
    }

    $template = $form->getTemplate();

    return $template;
  }

  function getPermissionLevel($module){
    if (!isset($this->permissions))
      $this->loadPermission($module, $this->permissions);

    if (!isset($this->permissions[$module]))
	return NULL;

    return $this->permissions[$module]['permission_level'];
  }

  function savePermissions($module, $item_id){
    $table = Users_Permission::getItemPermissionTableName($module);
    $db = & new PHPWS_DB($table);
    $db->addWhere("item_id", $item_id);
    $db->delete();
    $db->reset();

    if (!isset($_POST['assigned_groups']) || !is_array($_POST['assigned_groups']))
      return;

    $groups = & $_POST['assigned_groups'];

    $db->addValue("item_id", $item_id);
    foreach ($groups as $group_id){
      $db->addValue("group_id", $group_id);
      $db->insert();
    }

  }
}

?>