<?php

define("DEFAULT_ITEMNAME", "common");

define("USER_ERROR",             -1);
define("USER_ERR_DUP_USERNAME",  -2);
define("USER_ERR_DUP_GROUPNAME", -3);
define("USER_ERR_PERM_TABLE",    -4);
define("USER_ERR_PERM_MISS",     -5);
define("USER_ERR_PERM_FILE",     -6);


define("FULL_PERMISSION",    2);
define("PARTIAL_PERMISSION", 1);
define("NO_PERMISSION",      0);



class PHPWS_User extends PHPWS_Item {
  var $_username    = NULL;
  var $_password    = NULL;
  var $_deity       = FALSE;
  var $_groups      = NULL;
  var $_permissions = array();
  var $_logged      = FALSE;

  function PHPWS_User($id=NULL){
    $exclude = array("_owner",
		     "_editor",
		     "_ip",
		     "_groups",
		     "_permissions",
		     "_logged"
		     );

    $this->addExclude($exclude);
    $this->setTable("users");

    if(isset($id)) {
      $this->setId($id);
      $this->init();
      $this->loadUserGroups();
    }
  }

  /**
   * Return a textual error message for a error code
   * Function is copied from DB.php in PEAR libs.
   *
   * @param integer $value error code
   *
   * @return string error message, or false if the error code was
   * not recognized
   */
  function errorMessage($value, $funcName=NULL){
    static $errorMessages;

    if (!isset($errorMessages)) {
      $errorMessages = array(
			     USER_ERROR             => "Unknown error",
			     USER_ERR_DUP_USERNAME  => "Duplicate user name",
			     USER_ERR_DUP_GROUPNAME => "Duplicate group name",
			     USER_ERR_PERM_TABLE    => "Permission table name already exists",
			     USER_ERR_PERM_MISS     => "Permission table not found",
			     USER_ERR_PERM_FILE     => "Module's permission file is missing"
			     );
    }
    
    if (PEAR::isError($value)) {
      $value = $value->getCode();
    }

    $message[] = "<b>Error:</b> in User Module - ";

    if (isset($errorMessages[$value]))
      $message[] = $errorMessages[$value];
    else
      $message[] = $errorMessages[PHPWS_DB_ERROR];

    if (isset($funcName))
      $message[] = " in function <b>$funcName()</b>";
    
    $message[] = ".";

    return implode("", $message);
  }

  function setUsername($username){
    $this->_username = $username;
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
    $login = PHPWS_User_Form::logBox($_SESSION['User']->isLogged());
    PHPWS_Layout::hold($login, "CNT_user_small", TRUE, -1);
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

  function &getGroups(){
    return $this->_groups;
  }


  function loadPermission($itemName){
    PHPWS_Core::initModClass("users", "Permission.php");
    $groups = &$this->getGroups();

    $permTable = PHPWS_User_Permission::getPermissionTableName($itemName);
    $itemTable = PHPWS_User_Permission::getItemPermissionTableName($itemName);

    PHPWS_DB::isTable($itemTable) ? $useItem = TRUE : $useItem = FALSE;

    if(!PHPWS_DB::isTable($permTable))
      return PEAR::raiseError($this->errorMessage(USER_ERR_PERM_MISS, "loadModulePermission"), USER_ERR_PERM_MISS, NULL, NULL, "Table Name: $permTable");

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
	    return FALSE;
	}
      } else
	return TRUE;
    } else
      return TRUE;
  }

  function save(){
    PHPWS_Core::initModClass("users", "Group.php");
    $username = &$this->getUsername();

    $DB = new PHPWS_DB("users");
    $DB->addWhere("username", $username);
    $result = $DB->select("one");

    if (isset($result)){
      if (PEAR::isError($result))
	return $result;
      else
	return PEAR::raiseError($this->errorMessage(USER_ERR_DUP_USERNAME, "save"), USER_ERR_DUP_USERNAME);
    }

    $DB = new PHPWS_DB("user_groups");
    $DB->addWhere("name", $username);
    $result = $DB->select("one");

    if (isset($result)){
      if (PEAR::isError($result))
	return $result;
      else
	return PEAR::raiseError($this->errorMessage(USER_ERR_DUP_GROUPNAME, "save"), USER_ERR_DUP_GROUPNAME);
    }
    
    $result = $this->commit();

    $group = new PHPWS_Group;
    $group->setName($username);
    $group->setUserId($this->getId());
    $group->setActive($this->isActive());
    $result = $group->save();

  }

  function loginUser($username, $password){
    // Note assuming here we are using the one username database
    // ie case insensitive

    $registrationScript = "default.php";

    include PHPWS_SOURCE_DIR . "mod/users/scripts/login/" . $registrationScript;

    if (!isset($logged) or $logged !== TRUE)
      return FALSE;

    if (isset($ID)){
      $_SESSION['User'] = new PHPWS_User($ID);
      $_SESSION['User']->setLogged(TRUE);
      PHPWS_User::getLogin();
      return TRUE;
    }
    
    return FALSE;
  }


  function logAnonymous(){
    $id = &PHPWS_User::getSetting('anonymous');
    $_SESSION['User'] = new PHPWS_User($id);
  }

  function &getSetting($setting){
    static $settings;

    if (!isset($settings)){
      $DB = new PHPWS_DB("users_settings");
      $settings = $DB->select("row");
    }

    return $settings[$setting];
  }

  function adminAction($command){
    switch ($command){
    case "main":
      PHPWS_Core::initModClass("users", "Form.php");
      PHPWS_User_Form::adminPanel();
      break;
    }
  }

  function disallow(){
    $title = "Sorry Charlie...";
    $content = "That section of the site is off limits to your type";
    PHPWS_Layout::add(array("TITLE"=>$title, "CONTENT"=>$content), "User_Main");
  }

}


?>