<?php

class PHPWS_Module {
  var $_title         = NULL;
  var $_proper_name   = NULL;
  var $_priority      = 50;
  var $_directory     = NULL;
  var $_version       = NULL;
  var $_active        = FALSE; 
  var $_image_dir     = NULL;
  var $_files_dir     = NULL;
  var $_register      = FALSE;
  var $_import_sql   = FALSE;

  function PHPWS_Module($title=NULL){
    if (isset($title)){
      $this->setTitle($title);
      $this->init();
    }
  }

  function init(){
    $title = $this->getTitle();

    $proper_name   = NULL;
    $version       = .001;
    $active        = TRUE;
    $image_dir     = NULL;
    $files_dir     = NULL;
    $register      = FALSE;
    $import_sql    = FALSE;

    $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");
    
    $result = PHPWS_Core::getConfigFile($title, "boost.php");
    if (PEAR::isError($result))
      return $result;

    include $result;
    
    $this->setProperName($proper_name);
    $this->setVersion($version);
    $this->setActive($active);
    $this->setImportSQL($import_sql);
    $this->setRegister($register);
  }


  function setTitle($title){
    $this->_title = trim($title);
  }

  function getTitle(){
    return $this->_title;
  }

  function setProperName($name){
    $this->_proper_name = $name;
  }

  function getProperName($useTitle=FALSE){
    if (!isset($this->_proper_name) && $useTitle == TRUE)
      return ucwords(str_replace("_", " ", $this->getTitle()));
    else
      return $this->_proper_name;
  }

  function setPriority($priority){
    $this->_priority = (int)$priority;
  }

  function getPriority(){
    return $this->_priority;
  }

  function setDirectory($directory){
    $this->_directory = $directory;
  }

  function getDirectory(){
    return $this->_directory;
  }

  function setVersion($version){
    $this->_version = $version;
  }

  function getVersion(){
    return $this->_version;
  }

  function setRegister($register){
    $this->_register = (bool)$register;
  }

  function isRegister(){
    return $this->_register;
  }

  function setImportSQL($sql){
    $this->_import_sql = (bool)$sql;
  }

  function isImportSQL(){
    return $this->_import_sql;
  }

  function setActive($active){
    $this->_active = (bool)$active;
  }

  function isActive(){
    return $this->_active;
  }

  function save(){
    $db = new PHPWS_DB("modules");
    $db->addWhere("title", $this->getTitle());
    $db->delete();
    $db->resetWhere();
    if (!$this->getProperName())
      $this->setProperName($this->getProperName(TRUE));

    return $db->saveObject($this, TRUE);
  }

}


?>