<?php

class File_Common {
  var $id         = NULL;
  var $directory  = NULL;
  var $filename   = NULL;
  var $type       = NULL;
  var $title      = NULL;
  var $size       = NULL;
  var $errors     = array();
  var $tmp_name   = NULL;
  var $_classtype = NULL;

  function setDirectory($directory){
    if (!preg_match("/\/$/", $directory))
      $directory .= "/";
    $this->directory = $directory;
  }

  function getDirectory(){
    return $this->directory;
  }

  function setFilename($filename){
    $this->filename = $filename;
  }

  function getFilename(){
    return $this->filename;
  }

  function setSize($size){
    $this->size = (int)$size;
  }

  function getSize(){
    return $this->size;
  }

  function setTmpName($name){
    $this->tmp_name = $name;
  }

  function getTmpName(){
    return $this->tmp_name;
  }

  function setTitle($title){
    $this->title($title);
  }

  function getPath(){
    if (empty($this->filename))
      return PHPWS_Error::get(PHPWS_FILENAME_NOT_SET, "core", "PHPWS_File::getPath");

    if (empty($this->directory))
      return PHPWS_Error::get(PHPWS_DIRECTORY_NOT_SET, "core", "PHPWS_File::getPath");

    return $this->getDirectory() . $this->getFilename();
  }

  function allowType($type=NULL){
    if ($this->_classtype == "doc")
      $typeList = unserialize(ALLOWED_DOC_TYPES);
    else
      $typeList = unserialize(ALLOWED_IMAGE_TYPES);

    if (!isset($type))
      $type = $this->getType();

    return in_array($type, $typeList);
  }

  function allowSize($size=NULL){
    if ($this->_classtype == "doc")
      $limit = unserialize(MAX_DOC_SIZE);
    else
      $limit = unserialize(MAX_IMAGE_SIZE);

    if (!isset($size))
      $size = $this->getSize();

    return ($size <= $limit) ? TRUE : FALSE;
  }


}

?>