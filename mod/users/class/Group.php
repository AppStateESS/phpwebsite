<?php

class PHPWS_Group {
  var $id           = NULL;
  var $name         = NULL;
  var $user_id      = 0;
  var $active       = FALSE;
  var $_members     = NULL;
  var $_permissions = NULL;
  var $_groups      = NULL;
  var $_error       = NULL;
  
  function PHPWS_Group($id=NULL, $loadGroups=TRUE){
    if (isset($id)){
      $this->setId($id);
      $result = $this->init();
      if (PEAR::isError($result)){
	$this->_error = $result;
	return;
      }
      $this->loadMembers();
      if ($loadGroups == TRUE)
	$this->loadGroups();
    }
  }

  function init(){
    $db = & new PHPWS_DB('users_groups');
    return $db->loadObject($this);
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setActive($active){
    $this->active = (bool)$active;
  }

  function isActive(){
    return (bool)$this->active;
  }

  function loadGroups(){
    $DB = & new PHPWS_DB('users_members');
    $DB->addWhere('member_id', $this->getId());
    $DB->addColumn('group_id');
    $result = $DB->select('col');

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return;
    }
    
    $this->setGroups($result);
  }

  function setGroups($groups){
    $this->_groups = $groups;
  }

  function getGroups(){
    return $this->_groups;
  }

  function loadMembers(){
    $db = & new PHPWS_DB('users_members');
    $db->addWhere('group_id', $this->getId());
    $db->addColumn('member_id');
    $result = $db->select('col');
    $this->setMembers($result);
  }

  function setName($name, $test=FALSE){
    if ($test == TRUE){
      if (empty($name) || preg_match('/[^\w\s]+/', $name))
	return PHPWS_Error::get(USER_ERR_BAD_GROUP_NAME, 'users', 'setName');

      if (strlen($name) < GROUPNAME_LENGTH)
	return PHPWS_Error::get(USER_ERR_BAD_GROUP_NAME, 'users', 'setName');

      $db = & new PHPWS_DB('users_groups');
      $db->addWhere('name', $name);
      $db->addWhere('id', $this->id, '!=');
      $result = $db->select('one');
      if (isset($result)){
	if(PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, 'users', 'setName');
      } else {
	$this->name = $name;
	return TRUE;
      }
    } else {
      $this->name = $name;
      return TRUE;
    }
  }

  function getName(){
    return $this->name;
  }

  function setUserId($id){
    $this->user_id = $id;
  }

  function getUserId(){
    return $this->user_id;
  }

  function setMembers($members){
    $this->_members = $members;
  }

  function dropMember($member){
    if (!is_array($this->_members))
      return;

    $key = array_search($member, $this->_members);
    unset($this->_members[$key]);
  }

  function dropAllMembers(){
    $db = & new PHPWS_DB('users_members');
    $db->addWhere('group_id', $this->getId());
    return $db->delete();
  }

  function addMember($member, $test=FALSE){
    if ($test == TRUE){
      $db = & new PHPWS_DB('users_groups');
      $db->addWhere('id', $member);
      $result = $db->select('one');
      if (isset($result)){
	if(PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_GROUP_DNE, 'users', 'addMember');
      } else {
	$this->_members[] = $member;
	return TRUE;
      }

      $result = $db->select('one');
    } else
      $this->_members[] = $member;
  }

  function getMembers(){
    return $this->_members;
  }

  function save(){
    $db = & new PHPWS_DB('users_groups');

    $result = $db->saveObject($this);
    $members = $this->getMembers();

    if (isset($members)){
      $this->dropAllMembers();
      $db = & new PHPWS_DB('users_members');
      foreach($members as $member){
	$db->addValue('group_id', $this->getId());
	$db->addValue('member_id', $member);
	$db->insert();
	$db->resetValues();
      }
    }
  }

  function kill()
  {
    $db = & new PHPWS_DB('users_groups');
    $db->addWhere('id', $this->id);
    $db->delete();
    $this->dropAllMembers();
  }

  function allow($itemName, $permission=NULL, $item_id=NULL, $itemname=NULL){
    PHPWS_Core::initModClass('users', 'Permission.php');

    if (!isset($this->_permission))
      $this->loadPermissions();

    return $this->_permission->allow($itemName, $permission, $item_id, $itemname);
  }

  function getPermissionLevel($module){
    PHPWS_Core::initModClass('users', 'Permission.php');

    if (!isset($this->_permission))
      $this->loadPermissions();

    return $this->_permission->getPermissionLevel($module);
  }

  function loadPermissions($loadAll=TRUE){
    if ($loadAll && isset($this->_groups))
      $groups = $this->_groups;

    $groups[] = $this->getId();
    $this->_permission = & new Users_Permission($groups);
  }

}

?>