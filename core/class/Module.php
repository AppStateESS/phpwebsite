<?php

class PHPWS_Module {
  var $_title         = NULL;
  var $_proper_name   = NULL;
  var $_priority      = 50;
  var $_directory     = NULL;
  var $_version       = NULL;
  var $_active        = TRUE; 
  var $_image_dir     = TRUE;
  var $_file_dir      = TRUE;
  var $_register      = FALSE;
  var $_import_sql    = FALSE;
  var $_version_http  = NULL;
  var $_about         = FALSE;
  var $_pre94         = FALSE;
  var $_fullMod       = TRUE;

  function PHPWS_Module($title=NULL){
    if (isset($title)){
      $this->setTitle($title);
      $this->init();
    }
  }

  function init(){
    $title = $this->getTitle();

    $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");
    
    $result = PHPWS_Core::getConfigFile($title, "boost.php");
    if (PEAR::isError($result)){
      $this->_fullMod = FALSE;
      return $result;
    }

    include $result;

    if (isset($mod_title)){
      $this->_pre94 = TRUE;
      $proper_name = $mod_pname;
      if (!isset($active)|| $active == 'on')
	$active = TRUE;
      else
	$active == FALSE;
    }

    if (isset($proper_name))
      $this->setProperName($proper_name);

    if (isset($priority))
      $this->setPriority($priority);

    if (isset($version))
      $this->setVersion($version);

    if (isset($active))
      $this->setActive($active);

    if (isset($import_sql))
      $this->setImportSQL($import_sql);

    if ($this->isPre94())
      $this->setImportSQL(FALSE);

    if (isset($image_dir))
      $this->setImageDir($image_dir);

    if (isset($file_dir))
      $this->setFileDir($file_dir);

    if (isset($register))
      $this->setRegister($register);

    if (isset($version_http))
      $this->setVersionHttp($version_http);

    if (isset($about))
      $this->setAbout($about);

    if (isset($api))
      $this->setAPI($api);
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

  function setImageDir($switch){
    $this->_image_dir = (bool)$switch;
  }

  function isImageDir(){
    return $this->_image_dir;
  }

  function setFileDir($switch){
    $this->_file_dir = (bool)$switch;
  }

  function isFileDir(){
    return $this->_file_dir;
  }

  function setActive($active){
    $this->_active = (bool)$active;
  }

  function isActive(){
    return $this->_active;
  }

  function setAbout($about){
    $this->_about = (bool)$about;
  }

  function isAbout(){
    return $this->_about;
  }

  function isPre94(){
    return $this->_pre94;
  }

  function setAPI($api){
    $this->_api = $api;
  }

  function setVersionHttp($http){
    $this->_version_http = $http;
  }

  function getVersionHttp(){
    return $this->_version_http;
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

  function isInstalled(){
    $db = & new PHPWS_DB("modules");
    $db->addWhere("title", $this->getTitle());
    $result = $db->select("row");
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    } else
      return isset($result);
  }

  function needsUpgrade(){
    $db = & new PHPWS_DB("modules");
    $db->addWhere("title", $this->getTitle());
    $result = $db->select("row");
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    }

    return ($result['version'] < $this->getVersion() ? TRUE : FALSE);
  }
  
  function isFullMod(){
    return $this->_fullMod;
  }
}

?>