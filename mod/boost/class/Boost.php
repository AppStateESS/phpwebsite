<?php

PHPWS_Core::initCoreClass("Module.php");
$result = PHPWS_Core::getConfigFile("boost", "config.php");

if (PEAR::isError($result)){
  PHPWS_Error::log($result);
  PHPWS_Core::errorPage();
}

define("BOOST_ERR_NOT_MODULE",    -1);
define("BOOST_ERR_NO_INSTALLSQL", -2);

require_once $result;

class PHPWS_Boost {
  var $module = NULL;
  var $log    = array();

  function loadModule($module){
    $this->module = & new PHPWS_Module($module);
    $result = $this->module->init();

    if (PEAR::isError($result))
      return $result;

    return TRUE;
  }

  function setModule($module){
    if (!is_object($module) || get_class($module) != "phpws_module")
      return PHPWS_Error::get(BOOST_ERR_NOT_MODULE, "boost", "setModule");

    $this->module = $module;
  }

  function install($register=TRUE){
    if (!PHPWS_Boost::isLocked($this->module->getTitle())){
      if ($this->module->isImportSQL()){
	$this->addLog(_("Importing SQL file."), TRUE);
	$result = $this->importSQL("install.sql");
	if (PEAR::isError($result)){
	  $this->addLog(_("A fatal error occurred."), TRUE);
	  $this->addLog($result->getMessage(), TRUE);
	  PHPWS_Error::log($result);
	  return FALSE;
	} else
	  $this->addLog(_("Import successful."), TRUE);
      }

      $this->addLog(_("Registering module to core."), TRUE);
      $result = $this->registerModule();
      
      if (PEAR::isError($result)){
	$this->addLog(_("A fatal error occurred."), TRUE);
	$this->addLog($result->getMessage(), TRUE);
	PHPWS_Error::log($result);
	return FALSE;
      } else
	$this->addLog(_("Registration successful."), TRUE);
    }

    $onInstallFile = PHPWS_SOURCE_DIR . "mod/" . $this->module->getTitle() . "/boost/install.php";

    if (is_file($onInstallFile)){
      $this->addLog(_("Processing installation file."), TRUE);
      PHPWS_Boost::onInstall($onInstallFile);
    } else
      PHPWS_Boost::finish($this->module->getTitle());

    return TRUE;
  }

  function toInstall($modules){
    if (!isset($_SESSION['Install_Modules'])){
      if (PHPWS_DB::isTable("modules")){
	$db = & new PHPWS_DB("modules");
	$db->addColumn("title");
	$mods = $db->select("col");
      } else
	$mods = array();

      foreach ($modules as $title){
	if (in_array(trim($title), $mods))
	  $_SESSION['Install_Modules'][trim($title)] = TRUE;
	else
	  $_SESSION['Install_Modules'][trim($title)] = FALSE;
      }
    }
    return $_SESSION['Install_Modules'];
  }

  function onInstall($file){
    include_once($file);

    if (function_exists("onInstall")){
      $result = onInstall();
      $this->addLog($result);
    }
  }

  function registerModule(){
    $db = new PHPWS_DB("modules");
    $db->addWhere("title", $this->module->getTitle());
    $db->delete();
    $db->resetWhere();
    $result = $db->saveObject($this->module, TRUE);
    return $result;
  }


  function importSQL($file){
    require_once "File.php";
    $sqlFile = $this->module->getDirectory() . "boost/$file";

    if (!is_file($sqlFile))
      return PHPWS_Error::get(BOOST_ERR_NO_INSTALLSQL, "boost", "importSQL", "File: " . $sqlFile);

    $sql = File::readAll($sqlFile);

    $result = PHPWS_DB::import($sql);
    return $result;
  }

  function addLog($message, $write=FALSE){
    $this->log[] = $message;

    if ($write == TRUE){
      $message = _("Module") . " - " . $this->module->getProperName(TRUE) . " : " . $message;
      PHPWS_Core::log($message, "boost.log");
    }
  }

  function getLog(){
    $result = implode("\n<br />", $this->log);
    $this->log = array();
    return $result;
  }

  function lock($module){
    $_SESSION['Boost_Hold'][$module] = TRUE;
  }

  function isLocked($module){
    if (isset($_SESSION['Boost_Hold'][$module]))
      return $_SESSION['Boost_Hold'][$module];
    return FALSE;
  }


  function finish($module){
    PHPWS_Boost::unlock($module);
  }

  function unlock($module){
    $_SESSION['Boost_Hold'][$module] = FALSE;
    $_SESSION['Install_Modules'][$module] = TRUE;
  }

}

?>