<?php

class File_Common {
  var $id          = NULL;
  var $filename    = NULL;
  var $directory   = NULL;
  var $type        = NULL;
  var $title       = NULL;
  var $description = NULL;
  var $size        = NULL;
  var $module      = NULL;
  var $_errors     = array();
  var $_tmp_name   = NULL;
  var $_classtype  = NULL;

  function init(){
    if (!isset($this->id))
      return FALSE;

    if ($this->_classtype == "image")
      $table = "images";
    elseif ($this->_classtype == "doc")
      $table = "documents";
    else
      return FALSE;

    $db = & new PHPWS_DB($table);
    $db->addWhere("id", $this->id);
    return $db->loadObject($this);
  }


  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

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
    $this->_tmp_name = $name;
  }

  function getTmpName(){
    return $this->_tmp_name;
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

  function setModule($module){
    $this->module = $module;
    if (empty($this->directory))
      $this->setDirectory($module);
  }

  function getModule(){
    return $this->module;
  }

  function setClassType($type){
    $this->_classtype = $type;
  }

  function getClassType(){
    return $this->_classtype;
  }

  function getPath(){
    if (empty($this->filename))
      return PHPWS_Error::get(PHPWS_FILENAME_NOT_SET, "core", "PHPWS_File::getPath");

    if (empty($this->directory))
      return PHPWS_Error::get(PHPWS_DIRECTORY_NOT_SET, "core", "PHPWS_File::getPath");

    return "./images/" . $this->getDirectory() . $this->getFilename();
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
      $limit = MAX_DOC_SIZE;
    else
      $limit = MAX_IMAGE_SIZE;

    if (!isset($size))
      $size = $this->getSize();

    return ($size <= $limit) ? TRUE : FALSE;
  }

  function fileIsSet($varName){
    return (empty($_FILES) || empty($_FILES[$varName]['name'])) ? FALSE : TRUE;
  }

  function getFILES($varName){
    if (!$this->fileIsSet($varName))
      return PHPWS_Error::get(PHPWS_FILE_NO_FILES, "core", "PHPWS_File::getFILES");

    $this->filename = preg_replace("/[^\w\.]/", "_", $_FILES[$varName]['name']);
    $this->setSize($_FILES[$varName]['size']);
    $this->setTmpName($_FILES[$varName]['tmp_name']);
    $this->setType($_FILES[$varName]['type']);
  }

  function write(){
    $temp_dir = $this->getTmpName();
    $path = $this->getPath();

    if (empty($temp_dir))
      return PHPWS_Error::get(PHPWS_FILE_NO_TMP, "core", "PHPWS_image::write", $path);

    if (PEAR::isError($path))
      return $path;

    if(!@move_uploaded_file($temp_dir, $path))
      return PHPWS_Error::get(PHPWS_FILE_DIR_NONWRITE, "core", "PHPWS_image::writeImage", $path);

    return TRUE;
  }

}

?>