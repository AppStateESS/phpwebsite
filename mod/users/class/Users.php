<?php

class PHPWS_User extends PHPWS_Item {
  var $_username    = NULL;
  var $_password    = NULL;
  var $_deity       = FALSE;
  var $_groups      = NULL;
  var $_permissions = array();
  var $_logged      = FALSE;
  var $_modSettings = NULL;
 
  function PHPWS_User($id=NULL){
    $exclude = array("_owner",
		     "_editor",
		     "_ip",
		     "_groups",
		     "_permissions",
		     "_logged",
		     "_modSettings"
		     );

    $this->addExclude($exclude);
    $this->setTable("users");

    if(isset($id)) {
      $this->setId($id);
      $this->init();
      $this->loadUserGroups();
    }
  }

  function setUsername($username, $checkDuplicate=FALSE){
    if (preg_match("/^[a-z]+[a-z0-9_]{3}$/iU", $username)){
      if ((bool)$checkDuplicate == TRUE){
	$DB = new PHPWS_DB("users");
	$DB->addWhere("username", $username);
	$result = $DB->select("one");
	if (isset($result) && !PEAR::isError($result))
	  return PHPWS_Error::get(USER_ERR_DUP_USERNAME, "users", "setUsername");
      }
	$this->_username = $username;
	return TRUE;
    }
    else 
      return PHPWS_Error::get(USER_ERR_BAD_USERNAME, "users", "setUsername");
  }

  function getUsername(){
    return $this->_username;
  }

  function setPassword($password, $hashPass=TRUE){
    if ($hashPass)
      $this->_password = md5($password);
    else
      $this->_password = $password;
  }

  function checkPassword($pass1, $pass2){
    if ($pass1 != $pass2)
      return PHPWS_Error::get(USER_ERR_PASSWORD_MATCH, "users", "checkPassword");
    elseif(strlen($pass1) < PASSWORD_LENGTH)
      return PHPWS_Error::get(USER_ERR_PASSWORD_LENGTH, "users", "checkPassword");
    elseif(preg_match("/(" . implode("|", unserialize(BAD_PASSWORDS)) . ")/i", $pass1))
      return PHPWS_Error::get(USER_ERR_PASSWORD_EASY, "users", "checkPassword");
    else
      return TRUE;
  }

  function getPassword(){
    return $this->_password;
  }

  function setLogged($status){
    $this->_logged = $status;
  }

  function isLogged(){
    return $this->_logged;
  }

  function setDeity($deity){
    $this->_deity = (bool)$deity;
  }

  function isDeity(){
    return $this->_deity;
  }

  function getLogin(){
    PHPWS_Core::initModClass("users", "Form.php");
    $login = User_Form::logBox($_SESSION['User']->isLogged());
    Layout::hold($login, "CNT_user_small", TRUE, -1);
  }


  function loadUserGroups(){
    $DB = & new PHPWS_DB("user_groups");
    $DB->addWhere("user_id", $this->getId());
    $DB->addColumn("id");
    $group = $DB->select("one");
    if (PEAR::isError($group)){
      echo $group->getMessage();
      return;
    }
    $groupList[] = $group;

    $DB = & new PHPWS_DB("user_members");
    $DB->addWhere("member_id", $group);
    $DB->addColumn("group_id");
    $result = $DB->select("col");

    if (PEAR::isError($group)){
      echo $group->getMessage();
      return;
    }
    
    if (is_array($result))
      $groupList = array_merge($result, $groupList);

    $this->setGroups($groupList);
  }


  function setGroups($groups){
    $this->_groups = $groups;
  }

  function getGroups(){
    return $this->_groups;
  }


  function loadPermission($itemName){
    PHPWS_Core::initModClass("users", "Permission.php");
    $groups = &$this->getGroups();

    $permTable = PHPWS_User_Permission::getPermissionTableName($itemName);
    $itemTable = PHPWS_User_Permission::getItemPermissionTableName($itemName);

    PHPWS_DB::isTable($itemTable) ? $useItem = TRUE : $useItem = FALSE;

    if(!PHPWS_DB::isTable($permTable))
      return PHPWS_Error::get(USER_ERR_PERM_MISS, "users", "loadModulePermission", "Table Name: $permTable");

    $permDB = new PHPWS_DB($permTable);
    $itemDB = new PHPWS_DB($itemTable);

    foreach ($groups as $group_id){
      if ($useItem)
	$itemDB->addWhere("group_id", $group_id, NULL, "or");

      $permDB->addWhere("group_id", $group_id, NULL, "or");
    }

    $permResult = $permDB->select();

    if ($useItem)
      $itemResult = $itemDB->select("col");

    if (PEAR::isError($itemResult))
      return $itemResult;

    if (!isset($itemResult))
      $itemResult = array();

    $permissionSet = array();
    foreach ($permResult as $permission){
      unset($permission['group_id']);
      foreach($permission as $name=>$value){
	if (!isset($permissionSet[$name]))
	  $permissionSet[$name] = $value;
	elseif ($permissionSet[$name] < $value)
	  $permissionSet[$name] = $value;
      }
    }
    
    $this->_permissions[$itemName]['items'] = $itemResult;
    $this->_permissions[$itemName]['permissions'] = $permissionSet;
    return TRUE;
  }


  function allow($itemName, $subpermission=NULL, $item_id=NULL){

    if ($this->isDeity())
      return TRUE;

    if (!isset($this->_permissions[$itemName]))
      $result = $this->loadPermission($itemName);

    if(isset($this->_permissions[$itemName]['permissions'])){
      if (isset($subpermission)){
	$allow = $this->_permissions[$itemName]['permissions'][$subpermission];
	if ($allow == FULL_PERMISSION)
	  return TRUE;
	elseif ($allow == PARTIAL_PERMISSION){
	  if (isset($item_id))
	    return in_array($item_id, $this->_permissions[$itemName]['items']);
	  else
	    return TRUE;
	}
      } else
	return TRUE;
    } else
      return TRUE;
  }

  function save(){
    PHPWS_Core::initModClass("users", "Group.php");
    $username = $this->getUsername();

    $DB = new PHPWS_DB("users");
    $DB->addWhere("username", $username);
    $result = $DB->select("one");

    if (isset($result)){
      if (PEAR::isError($result))
	return $result;
      else
	return PHPWS_Error::get(USER_ERR_DUP_USERNAME, "users", "save");
    }

    $DB = new PHPWS_DB("user_groups");
    $DB->addWhere("name", $username);
    $result = $DB->select("one");

    if (isset($result)){
      if (PEAR::isError($result))
	return $result;
      else
	return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, "users", "save");
    }
    
    $result = $this->commit();

    $group = new PHPWS_Group;
    $group->setName($username);
    $group->setUserId($this->getId());
    $group->setActive($this->isActive());
    $result = $group->save();

  }


  function disallow(){
    $title = "Sorry Charlie...";
    $content = "That section of the site is off limits to your type";
    Layout::add(array("TITLE"=>$title, "CONTENT"=>$content), "User_Main");
  }

  function logAnonymous(){
    PHPWS_Core::initModClass("users", "Action.php");
    $id = & User_Action::getSetting('anonymous');
    $_SESSION['User'] = new PHPWS_User($id);
  }

  /*********************** User Var Code *******************/
  function getVar($varName, $module){
    if (!$this->getID())
      return FALSE;

    if (!PHPWS_Core::moduleExists($module))
      return PHPWS_Error::get(USER_ERR_NO_MODULE, "users", "getUserVar");
    
    return (isset($this->modSettings[$module][$varName])) ? $this->modSettings[$module][$varName] : NULL;
  }


  function setVar($varName, $varValue, $module, $merge=FALSE){
    if (!$this->getID())
      return;

    if (!PHPWS_Core::moduleExists($module))
      return PHPWS_Error::get(USER_ERR_NO_MODULE, "users", "setUserVar");

    PHPWS_Core::initCoreMod("Text.php");

    if (!PHPWS_Text::isValidInput($varName))
      return PHPWS_Error::get(USER_ERR_BAD_VAR, "users", "setUserVar");

    if (!($id = $this->getID()))
      return FALSE;

    $currentVar = $this->getUserVar($varName, $module);
    
    if (is_array($currentVar) && is_array($varValue) && $merge == TRUE){
      foreach ($varValue as $key=>$value)
	$currentVar[$key] = $value;

      $varValue = $currentVar;
    }

    $DB = new PHPWS_DB("user_settings");
    $DB->addValue("module", $module);
    $DB->addValue("id", $id);
    $DB->addValue("var_name", $varName);
    $DB->addValue("var_value", $varValue);

    $this->dropUserVar($varName, $module);
    if ($DB->insert()){
      $this->_modSettings[$module][$varName] = $varValue;
      return TRUE;
    } else
      return FALSE;
  }

  function dropVar($varName, $module){
    if (!$this->getID())
      return;

    if (isset($this->modSettings[$module][$varName]))
      unset($this->modSettings[$module][$varName]);

    if (!PHPWS_Core::moduleExists($module))
      return PHPWS_Error::get(USER_ERR_NO_MODULE, "users", "setUserVar");

    if (!(PHPWS_Core::isValidInput($varName)))
      return PHPWS_Error::get(USER_ERR_BAD_VAR, "users", "setUserVar");

    $DB = new PHPWS_DB("user_settings");
    $DB->addWhere("module", $module);
    $DB->addWhere("id", $user->getID());
    $DB->addWhere("var_name", $varName);
    return $DB->delete();
  }

  function dropModule($module){
    if (!PHPWS_Core::moduleExists($module))
      return PHPWS_Error::get(USER_ERR_NO_MODULE, "users", "setUserVar");

    $DB = new PHPWS_DB("user_settings");
    $DB->addWhere("module", $module);
    return $DB->delete();
  }

  function dropUser(){
    $DB = new PHPWS_DB("user_settings");
    $DB->addWhere("id", $this->getID());
    return $DB->delete();
  }


}

?>