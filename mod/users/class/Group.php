<?php

class PHPWS_Group extends PHPWS_Item {
  var $_id;
  var $_name;
  var $_user_id;
  var $_members;
  
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
    $this->setTable("user_groups");

    if (isset($id))
      $this->init($id);

  }


  function setName($name){
    $this->_name = $name;
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

  function save(){
    $result = $this->commit();

    return $result;
  }

}

?>