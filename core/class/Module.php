<?php
/**
 * Class contains module information
 *
 * @version $Id$
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 */
class PHPWS_Module {
  var $title         = NULL;
  var $proper_name   = NULL;
  var $priority      = 50;
  var $directory     = NULL;
  var $version       = NULL;
  var $active        = TRUE; 
  var $image_dir     = TRUE;
  var $file_dir      = TRUE;
  var $register      = FALSE;
  var $unregister    = FALSE;
  var $import_sql    = FALSE;
  var $version_http  = NULL;
  var $about         = FALSE;
  var $pre94         = FALSE;
  var $fullMod       = TRUE;
  var $_error        = NULL;

  function PHPWS_Module($title=NULL, $file=TRUE){
    if (isset($title)){
      $this->setTitle($title);
      $this->init($file);
    }
  }

  function initByDB(){
    $db = & new PHPWS_DB('modules');
    $db->addWhere('title', $this->title);
    return $db->loadObject($this);
  }

  function initByFile(){
    $result = PHPWS_Core::getConfigFile($this->title, 'boost.php');
    if ($result == FALSE){
      $this->fullMod = FALSE;
      return $result;
    }

    include $result;
    
    if (isset($mod_title)){
      $this->pre94 = TRUE;
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

    if (isset($unregister))
      $this->setUnregister($unregister);

    if (isset($version_http))
      $this->setVersionHttp($version_http);

    if (isset($about))
      $this->setAbout($about);

    return TRUE;
  }

  function init($file=TRUE){
    $title = $this->getTitle();

    $this->setDirectory(PHPWS_SOURCE_DIR . "mod/$title/");

    if ($file == TRUE)
      $result = PHPWS_Module::initByFile();
    else
      $result = PHPWS_Module::initByDB();

    if (PEAR::isError($result))
      $this->_error = $result;
  }


  function setTitle($title){
    $this->title = trim($title);
  }

  function getTitle(){
    return $this->title;
  }

  function setProperName($name){
    $this->proper_name = $name;
  }

  function getProperName($useTitle=FALSE){
    if (!isset($this->proper_name) && $useTitle == TRUE)
      return ucwords(str_replace('_', ' ', $this->getTitle()));
    else
      return $this->proper_name;
  }

  function setPriority($priority){
    $this->priority = (int)$priority;
  }

  function getPriority(){
    return $this->priority;
  }

  function setDirectory($directory){
    $this->directory = $directory;
  }

  function getDirectory(){
    return $this->directory;
  }

  function setVersion($version){
    $this->version = $version;
  }

  function getVersion(){
    return $this->version;
  }

  function setRegister($register){
    $this->register = (bool)$register;
  }

  function isRegister(){
    return $this->register;
  }

  function setUnregister($unregister){
    $this->unregister = (bool)$unregister;
  }

  function isUnregister(){
    return $this->unregister;
  }

  function setImportSQL($sql){
    $this->import_sql = (bool)$sql;
  }

  function isImportSQL(){
    return $this->import_sql;
  }

  function setImageDir($switch){
    $this->image_dir = (bool)$switch;
  }

  function isImageDir(){
    return $this->image_dir;
  }

  function setFileDir($switch){
    $this->file_dir = (bool)$switch;
  }

  function isFileDir(){
    return $this->file_dir;
  }

  function setActive($active){
    $this->active = (bool)$active;
  }

  function isActive(){
    return $this->active;
  }

  function setAbout($about){
    $this->about = (bool)$about;
  }

  function isAbout(){
    return $this->about;
  }

  function isPre94(){
    return $this->pre94;
  }

  function setVersionHttp($http){
    $this->version_http = $http;
  }

  function getVersionHttp(){
    return $this->version_http;
  }

  function save(){
    $db = new PHPWS_DB('modules');
    $db->addWhere('title', $this->getTitle());
    $db->delete();
    $db->resetWhere();
    if (!$this->getProperName())
      $this->setProperName($this->getProperName(TRUE));

    return $db->saveObject($this);
  }

  function isInstalled(){
    $db = & new PHPWS_DB('modules');
    $db->addWhere('title', $this->getTitle());
    $result = $db->select('row');
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    } else
      return isset($result);
  }

  function needsUpdate(){
    $db = & new PHPWS_DB('modules');
    $db->addWhere('title', $this->getTitle());
    $result = $db->select('row');
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return FALSE;
    }

    return ($result['version'] < $this->getVersion() ? TRUE : FALSE);
  }
  
  function isFullMod(){
    return $this->fullMod;
  }
}

?>