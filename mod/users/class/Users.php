<?php

PHPWS_Core::initModClass("users", "Permission.php");

class PHPWS_User extends PHPWS_Item {
  var $_username     = NULL;
  var $_password     = NULL;
  var $_deity        = FALSE;
  var $_groups       = NULL;
  var $_permission   = NULL;
  var $_logged       = FALSE;
  var $_last_logged  = NULL;
  var $_settings     = NULL;
 
  function PHPWS_User($id=NULL){
    $exclude = array("_owner",
		     "_editor",
		     "_ip",
		     "_groups",
		     "_permissions",
		     "_logged",
		     "_last_logged",
		     "_settings"
		     );

    $this->addExclude($exclude);
    $this->setTable("users");

    if(isset($id) && is_numeric($id)) {
      $this->setId($id);
      $result = $this->init();
      if (PEAR::isError($result)){
	$this = $result;
	return FALSE;
      }
      $this->loadUserGroups();
    }
  }

  function setUsername($username, $checkDuplicate=FALSE){
    if (empty($username) || preg_match("/\W+/", $username))
      return PHPWS_Error::get(USER_ERR_BAD_USERNAME, "users", "setUsername");

    if (strlen($username) < USERNAME_LENGTH)
      return PHPWS_Error::get(USER_ERR_BAD_USERNAME, "users", "setUsername");

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
    if (empty($pass1) || empty($pass2))
      return PHPWS_Error::get(USER_ERR_PASSWORD_LENGTH, "users", "checkPassword");
    elseif ($pass1 != $pass2)
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
    return (bool)$this->_logged;
  }

  function isUser(){
    return isset($this->_id);
  }

  function setDeity($deity){
    $this->_deity = (bool)$deity;
  }

  function isDeity(){
    return $this->_deity;
  }


  function getSettings(){
    return $this->_settings;
  }

  function getLogin(){
    PHPWS_Core::initModClass("users", "Form.php");
    $login = User_Form::logBox($_SESSION['User']->isLogged());
    Layout::hold($login, "CNT_user_small", TRUE, -1);
  }

  function loadUserGroups(){
    $DB = & new PHPWS_DB("users_groups");
    $DB->addWhere("user_id", $this->getId());
    $DB->addColumn("id");
    $group = $DB->select("one");
    if (PEAR::isError($group)){
      echo $group->getMessage();
      return;
    }
    $groupList[] = $group;

    $DB = & new PHPWS_DB("users_members");
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

  function allow($itemName, $subpermission=NULL, $item_id=NULL){
    PHPWS_Core::initModClass("users", "Permission.php");
    if ($this->isDeity())
      return TRUE;

    if (!isset($this->_permission)){
      $groups = &$this->getGroups();
      $this->_permission = & new Users_Permission($groups);
    }

    return $this->_permission->allow($itemName, $subpermission, $item_id);
  }

  function save(){
    $newUser = FALSE;
    PHPWS_Core::initModClass("users", "Group.php");
    $username = $this->getUsername();

    if (!isset($this->_id)){
      $newUser = TRUE;
      $DB = new PHPWS_DB("users");
      $DB->addWhere("username", $username);
      $result = $DB->select("one");

      if (isset($result)){
	if (PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_DUP_USERNAME, "users", "save");
      }

      $DB = new PHPWS_DB("users_groups");
      $DB->addWhere("name", $username);
      $result = $DB->select("one");

      if (isset($result)){
	if (PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, "users", "save");
      }
    }

    $result = $this->commit();

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, "users", "save");
    }

    $this->saveVar();

    if ($newUser)
      return $this->createGroup();

    return TRUE;

  }

  function createGroup(){
    $group = new PHPWS_Group;
    $group->setName($this->getUsername());
    $group->setUserId($this->getId());
    $group->setActive($this->isActive());
    $result = $group->save();
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      $this->kill();
      return PHPWS_Error::get(USER_ERR_USER_NOT_SAVED, "users", "save");
    } else
      return TRUE;
  }

  function getUserGroup(){
    $db = & new PHPWS_DB("users_groups");
    $db->addWhere("user_id", $this->getId());
    $db->addColumn("id");
    $result = $db->select("one");
    if (PEAR::isError($result))
      return $result;
    elseif (!isset($result))
      return PHPWS_Error::get(USER_ERR_MISSING_GROUP, "users", "getUserGroup");
    else
      return $result;
  }

  function isAnonymous(){
    return (User_Action::getUserConfig('anonymous') == $this->getId() ? TRUE : FALSE);
  }

  function disallow(){
    $title = "Sorry Charlie...";
    $content = "That section of the site is off limits to your type";
    Layout::add(array("TITLE"=>$title, "CONTENT"=>$content), "User_Main");
  }

  function logAnonymous(){
    PHPWS_Core::initModClass("users", "Action.php");
    $id = User_Action::getUserConfig('anonymous');
    $_SESSION['User'] = new PHPWS_User($id);
  }

  /*********************** User Var Code *******************/
  function getVar($varName, $label){
    if ($this->isAnonymous())
      return FALSE;

    return (isset($this->_settings[$label][$varName])) ? $this->_settings[$label][$varName] : NULL;
  }


  function setVar($varName, $varValue, $label, $merge=FALSE){
    if ($this->isAnonymous())
      return FALSE;

    PHPWS_Core::initCoreClass("Text.php");

    if (!PHPWS_Text::isValidInput($varName))
      return PHPWS_Error::get(USER_ERR_BAD_VAR, "users", "setUserVar");

    if ($merge == TRUE){
      $currentVar = $this->getUserVar($varName, $label);
      
      if (is_array($currentVar) && is_array($varValue)){
	foreach ($varValue as $key=>$value)
	  $currentVar[$key] = $value;
	
	$varValue = $currentVar;
      }
    }

    $this->_settings[$label][$varName] = $varValue;
  }
   

  function saveVar(){
    if ($this->isAnonymous())
      return FALSE;

    $settings = $this->getSettings();
    if (!isset($settings))
      return TRUE;

    $DB = new PHPWS_DB("users_settings");

    foreach ($settings as $label => $varset){
      foreach ($varset as $varName => $varValue){
	$this->dropVar($varName, $label);
	$DB->addValue("id", $this->getId());
	$DB->addValue("label", $label);
	$DB->addValue("var_name", $varName);
	$DB->addValue("var_value", $varValue);
	$result = $DB->insert();
	$DB->resetValues();
	if (PEAR::isError($result))
	  PHPWS_Error::log($result);
      }
    }
    return TRUE;
  }

  function dropVar($varName, $label){
    PHPWS_Core::initCoreClass("Text.php");
    if ($this->isAnonymous())
      return FALSE;

    if (isset($this->_settings[$label][$varName]))
      unset($this->_settings[$label][$varName]);

    if (!(PHPWS_Text::isValidInput($varName)))
      return PHPWS_Error::get(USER_ERR_BAD_VAR, "users", "setUserVar");

    $DB = new PHPWS_DB("users_settings");
    $DB->addWhere("label", $label);
    $DB->addWhere("id", $this->getID());
    $DB->addWhere("var_name", $varName);
    return $DB->delete();
  }

  function dropLabel($label){
    $DB = new PHPWS_DB("users_settings");
    $DB->addWhere("label", $label);
    return $DB->delete();
  }

  function dropUser(){
    $DB = new PHPWS_DB("users_settings");
    $DB->addWhere("id", $this->getID());
    return $DB->delete();
  }


}

?>