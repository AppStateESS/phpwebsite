<?php

class PHPWS_File extends PHPWS_Item {
  var $_directory   = NULL;
  var $_filename    = NULL;
  var $_title       = NULL;
  var $_type        = NULL;

  function setDirectory($directory){
    if (!preg_match("/\/$/", $directory))
      $directory = $directory . "/";

    $this->_directory = $directory;
  }

  function getDirectory(){
    return $this->_directory;
  }

  function setFilename($name){
    $this->_filename = $name;
  }

  function getFilename(){
    return $this->_filename;
  }

  function getPath(){
    return $this->getDirectory() . $this->getFilename();
  }

  function setTitle($title){
    $this->_title = $title;
  }

  function getTitle(){
    return $this->_title;
  }

  function _setType($type){
    $this->_type = $type;
  }

  function getType(){
    return $this->_type;
  }

}

?>
