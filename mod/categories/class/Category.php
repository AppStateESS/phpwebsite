<?php

class Category{
  var $id          = NULL;
  var $title       = NULL;
  var $description = NULL;
  var $parent      = NULL;
  var $image       = NULL;
  var $thumbnail   = NULL;


  function Category($id=NULL){
    if (empty($id))
      return;

    $this->setId($id);
    $result = $this->init();
    if (PEAR::isError($result))
      PHPWS_Error::log($result);
  }
  
  function init(){
    $db = & new PHPWS_DB("categories");
    $db->addWhere("id", $this->id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setTitle($title){
    $this->title = $title;
  }

  function getTitle(){
    return $this->title;
  }

  function setDescription($description){
    $this->description = $description;
  }

  function getDescription(){
    return $this->description;
  }

  function setParent($parent){
    $this->parent = (int)$parent;
  }

  function getParent(){
    return $this->parent;
  }

  function setImage($image){
    $this->image = $image;
  }

  function getImage(){
    return $this->image;
  }

  function setThumbnail($thumbnail){
    $this->thumbnail = $thumbnail;
  }

  function getThumbnail(){
    return $this->thumbnail;
  }
  
  function save(){
    echo phpws_debug::testobject($this);
  }

}

?>