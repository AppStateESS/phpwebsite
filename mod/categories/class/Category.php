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

    $this->loadImage();
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setTitle($title){
    $this->title = strip_tags($title);
  }

  function getTitle(){
    return $this->title;
  }

  function setDescription($description){
    $this->description = PHPWS_Text::parseInput($description);
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

    if (is_numeric($image))
      $this->loadImage();
  }

  function getImage(){
    return $this->image;
  }

  function loadImage(){
    PHPWS_Core::initCoreClass("Image.php");
    $this->image = new PHPWS_Image($this->image);
  }

  function setThumbnail($thumbnail){
    $this->thumbnail = $thumbnail;
  }

  function getThumbnail(){
    return $this->thumbnail;
  }
  
  function save(){
    $db = & new PHPWS_DB("categories");

    if (isset($this->id))
      $db->addWhere("id", $this->id);

    $tmpImage = $this->image;
    $this->image = $this->image->getId();
    $result = $db->saveObject($this);
    $this->image = $tmpImage;
    return $result;
  }

  function kill(){
    if (empty($this->id))
      return FALSE;
    $db = & new PHPWS_DB("categories");
    $db->addWhere("id", $this->id);
    return $db->delete();
  }
}

?>