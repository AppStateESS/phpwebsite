<?php

class PHPWS_Group extends PHPWS_Item {
  var $_id       = NULL;
  var $_name     = NULL;
  var $_user_id  = NULL;
  var $_members  = NULL;
  
  function PHPWS_Group($id=NULL){
    $excludes = array (
		       "_owner",
		       "_editor",
		       "_ip",
		       "_created",
		       "_updated",
		       "_approved",
		       "_members"
		       );

    $this->addExclude($excludes);
    $this->setTable("users_groups");

    if (isset($id)){
      $this->init($id);
    }
  }


  function setName($name, $test=FALSE){
    if ($test == TRUE){
      if (empty($name) || preg_match("/\W+/", $name))
	return PHPWS_Error::get(USER_ERR_BAD_GROUP_NAME, "users", "setName");

      if (strlen($name) < GROUPNAME_LENGTH)
	return PHPWS_Error::get(USER_ERR_BAD_GROUP_NAME, "users", "setName");

      $db = & new PHPWS_DB("users_groups");
      $db->addWhere("name", $name);
      $result = $db->select("one");
      if (isset($result)){
	if(PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_DUP_GROUPNAME, "users", "setName");
      } else {
	$this->_name = $name;
	return TRUE;
      }
    } else {
      $this->_name = $name;
      return TRUE;
    }
  }

  function getName(){
    return $this->_name;
  }

  function setUserId($id){
    $this->_user_id = $id;
  }

  function getUserId(){
    return $this->_user_id;
  }

  function addMember($member, $test=FALSE){
    if ($test == TRUE){
      $db = & new PHPWS_DB("users_groups");
      $db->addWhere("id", $member);
      $result = $db->select("one");
      if (isset($result)){
	if(PEAR::isError($result))
	  return $result;
	else
	  return PHPWS_Error::get(USER_ERR_GROUP_DNE, "users", "addMember");
      } else {
	$this->_members[] = $member;
	return TRUE;
      }

      $result = $db->select("one");
    } else
      $this->_members[] = $member;
  }

  function getMembers(){
    return $this->_members;
  }

  function save(){
    $result = $this->commit();

    return $result;
  }

}

?>