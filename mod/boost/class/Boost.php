<?php

PHPWS_Core::initCoreClass("Module.php");
require_once PHPWS_Core::getConfigFile("boost", "config.php");

class PHPWS_Boost {
  var $module = NULL;
  var $log    = array();

  function setModule($module){
    if (!is_object($module) || get_class($module) != "phpws_module")
      return PHPWS_Error::get(BOOST_ERR_NOT_MODULE, "boost", "setModule");

    $this->module = $module;
  }

  function install(){
    if ($this->module->isInstallSQL()){
      $this->log(_("Importing SQL file."), TRUE);
      $this->importSQL();
    }
  }

  function importSQL(){
    require_once "File.php";
    $sqlFile = $this->module->getDirectory() . "boost/install.sql";
    if (!is_file($sqlFile))
      return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, "boost", "importSQL", "File: " . $sqlFile);

    $sql = File::readAll($sqlFile);

    $result = PHPWS_DB::import($sql);
  }

  function log($message, $write=FALSE){
    $this->log[] = $message;

    if ($write == TRUE){
      $message = _("Module") . "-" . $this->module->getTitle() . " : " . $message;
      PHPWS_Core::log($message, "boost.log");
    }
  }


}

?>