<?php

class PHPWS_Module {
  var $_title         = NULL;
  var $_directory     = NULL;
  var $_version       = NULL;
  var $_active        = FALSE; 
  var $_image_dir     = NULL;
  var $_files_dir     = NULL;
  var $_register      = FALSE;
  var $_install_sql   = FALSE;
  var $_update_sql    = FALSE;
  var $_uninstall_sql = FALSE;

  function PHPWS_Module($module=NULL){
    if (isset($module)  && $module != "core")
      $this->init($module);
  }

  function init($module){
    $this->setTitle($module);
  }


  function setTitle($title){
    $this->_title = $title;
    $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");
  }

  function getTitle(){
    return $this->_title;
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

  function setInstallSQL($sql){
    $this->_install_sql = (bool)$sql;
  }

  function isInstallSQL(){
    return $this->_install_sql;
  }

}


?>