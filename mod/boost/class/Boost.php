<?php

PHPWS_Core::initCoreClass("Module.php");
$result = PHPWS_Core::getConfigFile("boost", "config.php");

if (PEAR::isError($result)){
  PHPWS_Error::log($result);
  PHPWS_Core::errorPage();
}

define("BOOST_NEW",      0);
define("BOOST_START",    1);
define("BOOST_PENDING",  2);
define("BOOST_DONE",     3);
define("PRE094_SUCCESS", 4);

require_once $result;

class PHPWS_Boost {
  var $modules       = NULL;
  var $status        = NULL;
  var $current       = NULL;
  var $installedMods = NULL;

  function addModule($module){
    if (!is_object($module) || get_class($module) != "phpws_module")
      return PHPWS_Error::get(BOOST_ERR_NOT_MODULE, "boost", "setModule");

    $this->modules[$module->getTitle()] = $module;
  }

  function loadModules($modules){
    foreach ($modules as $title){
      $mod = & new PHPWS_Module(trim($title));
      $this->addModule($mod);
      $this->setStatus($title, BOOST_NEW);
    }
  }

  function isFinished(){
    if (in_array(BOOST_NEW, $this->status)
	|| in_array(BOOST_START, $this->status)
	|| in_array(BOOST_PENDING, $this->status))
      return FALSE;

    return TRUE;
  }

  function getInstalledModules(){
    $db = & new PHPWS_DB("modules");
    $db->addColumn("title");
    $modules = $db->loadObjects("PHPWS_Module");
    return $modules;
  }


  function setStatus($title, $status){
    $this->status[trim($title)] = $status;
  }

  function getStatus($title){
    if (!isset($this->status[$title]))
      return NULL;

    return $this->status[$title];
  }

  function setCurrent($title){
    $this->current = $title;
  }

  function getCurrent(){
    return $this->current;
  }

  function isModules(){
    return isset($this->modules);
  }

  function install($inBoost=TRUE){
    $content = array();
    if (!$this->isModules())
      return PHPWS_Error::get(BOOST_NO_MODULES_SET, "boost", "install");

    foreach ($this->modules as $title => $mod){
      $title = trim($title);
      if ($this->getStatus($title) == BOOST_DONE)
	continue;

      if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW){
	$this->setCurrent($title);
	$this->setStatus($title, BOOST_START);
      }

      $content[] = "<b>" . _("Installing") . " - " . $mod->getProperName() ."</b>";

      if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()){
	$content[] = _("Importing SQL install file.");
	$result = PHPWS_Boost::importSQL($mod->getDirectory() . "boost/install.sql");

	if (is_array($result)){
	  foreach ($result as $error)
	    PHPWS_Error::log($error);

	  $content[] = _("An import error occurred.");
	  $content[] = _("Check your logs for more information.");
	  return implode("<br />", $content);
	  return;
	} else
	  $content[] = _("Import successful.");
      }

      $result = $this->onInstall($mod, $content);

      if ($result === TRUE){
	$this->setStatus($title, BOOST_DONE);
	$this->createDirectories($mod, $content);

	$this->registerModule($mod, $content);
	if ($inBoost == FALSE)
	  $content[] = PHPWS_Text::link("index.php?step=3", _("Continue installation..."));
	else
	  $content[] = _("Installation complete!");
	break;
      }
      elseif ($result === -1){
	$this->setStatus($title, BOOST_DONE);
	$this->createDirectories($mod, $content);
	$this->registerModule($mod, $content);
      }
      elseif ($result === FALSE){
	$this->setStatus($title, BOOST_PENDING);
	break;
      }
      elseif (PEAR::isError($result)){
	$content[] = _("There was a problem in the installation file:");
	$content[] = "<b>" . $result->getMessage() ."</b>";
	$content[] = "<br />";
	PHPWS_Error::log($result);
      }
    }

    return implode("<br />", $content);    
  }


  function onInstall($mod, &$installCnt){
    $onInstallFile = $mod->getDirectory() . "boost/install.php";
    $installFunction = $mod->getTitle() . "_install";
    if (!is_file($onInstallFile)){
      $this->addLog($mod->getTitle(), _("No installation file found."));
      return -1;
    }

    if ($this->getStatus($mod->getTitle()) == BOOST_START)
      $this->setStatus($mod->getTitle(), BOOST_PENDING);

    /**
     * If module was before 094, install differently
     */
    if ($mod->isPre94()){
      PHPWS_Core::initCoreClass("Crutch.php");
      PHPWS_Crutch::startSessions();
      $content = NULL;
      include_once($onInstallFile);
      $installCnt[] = $content;
      if ($status)
	return TRUE;
      else
	return PHPWS_Error::get(BOOST_FAILED_PRE94_INSTALL, "boost", "install");
    }

    include_once($onInstallFile);

    if (function_exists($installFunction)){
      $installCnt[] = _("Processing installation file.");
      return $installFunction($installCnt);
    }
    else
      return TRUE;
  }

  function uninstall(){
    $content = array();
    if (!$this->isModules())
      return PHPWS_Error::get(BOOST_NO_MODULES_SET, "boost", "install");

    foreach ($this->modules as $title => $mod){
      $title = trim($title);
      if ($this->getStatus($title) == BOOST_DONE)
	continue;

      if ($this->getCurrent() != $title && $this->getStatus($title) == BOOST_NEW){
	$this->setCurrent($title);
	$this->setStatus($title, BOOST_START);
      }

      $content[] = "<b>" . _("Uninstalling") . " - " . $mod->getProperName() ."</b>";

      if ($this->getStatus($title) == BOOST_START && $mod->isImportSQL()){
	$content[] = _("Importing SQL uninstall file.");
	$result = PHPWS_Boost::importSQL($mod->getDirectory() . "boost/uninstall.sql");

	if (PEAR::isError($result)){
	  PHPWS_Error::log($result);

	  $content[] = _("An import error occurred.");
	  $content[] = _("Check your logs for more information.");
	  return implode("<br />", $content);
	} else
	  $content[] = _("Import successful.");
      }

      $result = (bool)$this->onUninstall($mod, $content);

      if ($result === TRUE){
	$this->setStatus($title, BOOST_DONE);
	$this->removeDirectories($mod, $content);

	$this->unregisterModule($mod, $content);
	$content[] = _("Finished uninstalling module!");
	break;
      }
      elseif ($result == -1){
	$this->setStatus($title, BOOST_DONE);
	$this->removeDirectories($mod, $content);
	$this->unregisterModule($mod, $content);
      }
      elseif ($result === FALSE){
	$this->setStatus($title, BOOST_PENDING);
	break;
      }
      elseif (PEAR::isError($result)){
	$content[] = _("There was a problem in the installation file:");
	$content[] = "<b>" . $result->getMessage() ."</b>";
	$content[] = "<br />";
	PHPWS_Error::log($result);
      }

    }

    return implode("<br />", $content);    

  }

  function onUninstall($mod, &$uninstallCnt){
    $onUninstallFile = $mod->getDirectory() . "boost/uninstall.php";
    $installFunction = $mod->getTitle() . "_uninstall";
    if (!is_file($onUninstallFile)){
      $uninstallCnt[] = _("Uninstall file not found.");
      $this->addLog($mod->getTitle(), _("No uninstall file found."));
      return -1;
    }

    if ($this->getStatus($mod->getTitle()) == BOOST_START)
      $this->setStatus($mod->getTitle(), BOOST_PENDING);

    /**
     * If module was before 094, install differently
     */
    if ($mod->isPre94()){
      PHPWS_Core::initCoreClass("Crutch.php");
      PHPWS_Crutch::startSessions();
      $content = NULL;
      include_once($onUninstallFile);
      $uninstallCnt[] = $content;
      return $status;
    }

    include_once($onUninstallFile);

    if (function_exists($installFunction)){
      $uninstallCnt[] = _("Processing uninstall file.");
      return $installFunction($uninstallCnt);
    }
    else
      return TRUE;
  }


  function createDirectories($mod, &$content, $homeDir = NULL, $overwrite=FALSE){
    PHPWS_Core::initCoreClass("File.php");
    if (!isset($homeDir))
      $homeDir = getcwd();

    $configSource = $mod->getDirectory() . "conf/";
    if (is_dir($configSource)){
      $configDest   = $homeDir . "/config/" . $mod->getTitle() . "/";
      if ($overwrite == TRUE || !is_dir($configDest)){
	$content[] = _("Copying configuration files.");
	$this->addLog($mod->getTitle(), _print(_("Copying directory [var1] to [var2]"), array($configSource, $configDest)));
	PHPWS_File::recursiveFileCopy($configSource, $configDest);
	chdir($homeDir);
      }
    }

    $templateSource = $mod->getDirectory() . "templates/";
    if (is_dir($templateSource)){
      $templateDest   = $homeDir . "/templates/" . $mod->getTitle() . "/";
      if ($overwrite == TRUE || !is_dir($templateDest)){
	$content[] = _("Copying template files.");
	$this->addLog($mod->getTitle(), _print(_("Copying directory [var1] to [var2]"), array($templateSource, $templateDest)));
	PHPWS_File::recursiveFileCopy($templateSource, $templateDest);
	chdir($homeDir);
      }
    }

    if (!is_dir($homeDir . "/images/mod/")){
      $content[] = _("Creating module image directory.");
      $this->addLog($mod->getTitle(), _("Created directory") . " $homeDir/images/mod/");
      mkdir("$homeDir/images/mod");
    }

    if ($mod->isFileDir()){
      $filesDir = $homeDir . "/files/" . $mod->getTitle();
      if (!is_dir($filesDir)){
	$content[] = _("Creating files directory for module.");
	$this->addLog($mod->getTitle(), _("Created directory") . " " . $filesDir);
	mkdir($filesDir);
      }
    }

    if ($mod->isImageDir()){
      $imageDir = $homeDir . "/images/" . $mod->getTitle();
      if (!is_dir($imageDir)){
	$this->addLog($mod->getTitle(), _("Created directory") . " " . $imageDir);
	$content[] = _("Creating image directory for module.");
	mkdir($imageDir);
      }
    }

    $modSource = $mod->getDirectory() . "img/";
    if (is_dir($modSource)){
      $modImage = $homeDir . "/images/mod/" . $mod->getTitle() . "/";
      $this->addLog($mod->getTitle(), _print(_("Copying directory [var1] to [var2]"), array($modSource, $modImage)));
      $content[] = _("Copying source image directory for module.");

      PHPWS_File::recursiveFileCopy($modSource, $modImage);
      chdir($homeDir);
    }
  }

  function removeDirectories($mod, &$content, $homeDir = NULL){
    PHPWS_Core::initCoreClass("File.php");
    if (!isset($homeDir))
      $homeDir = getcwd();

    $configDir = "$homeDir/config/" . $mod->getTitle() . "/";
    if (is_dir($configDir)){
      $this->addLog($mod->getTitle(), _print(_("Removing directory [var1]"), $configDir));
      if(!PHPWS_File::rmdir($configDir))
	$this->addLog($mod->getTitle(), _print(_("Unable to removing directory [var1]"), $configDir));
    }

    $templateDir = "$homeDir/templates/" . $mod->getTitle() . "/";
    if (is_dir($templateDir)){
      $this->addLog($mod->getTitle(), _print(_("Removing directory [var1]"), $templateDir));
      if(!PHPWS_File::rmdir($templateDir))
	$this->addLog($mod->getTitle(), _print(_("Unable to removing directory [var1]"), $templateDir));
    }

    $imageDir = "$homeDir/images/" . $mod->getTitle() . "/";
    if (is_dir($imageDir)){
      $this->addLog($mod->getTitle(), _print(_("Removing directory [var1]"), $imageDir));
      if(!PHPWS_File::rmdir($imageDir))
	$this->addLog($mod->getTitle(), _print(_("Unable to removing directory [var1]"), $imageDir));
    }

    $fileDir = "$homeDir/files/" . $mod->getTitle() . "/";
    if (is_dir($fileDir)){
      $this->addLog($mod->getTitle(), _print(_("Removing directory [var1]"), $fileDir));
      if(!PHPWS_File::rmdir($fileDir))
	$this->addLog($mod->getTitle(), _print(_("Unable to removing directory [var1]"), $fileDir));
    }

    $imageModDir = "$homeDir/images/mod/" . $mod->getTitle() . "/";
    if (is_dir($imageModDir)){
      $this->addLog($mod->getTitle(), _print(_("Removing directory [var1]"), $imageModDir));
      if(!PHPWS_File::rmdir($imageModDir))
	$this->addLog($mod->getTitle(), _print(_("Unable to removing directory [var1]"), $imageModDir));
    }
    
  }


  function registerModule($module, &$content){
    $content[] = _("Registering module to core.");

    $db = new PHPWS_DB("modules");
    $db->addWhere("title", $module->getTitle());
    $db->delete();
    $db->resetWhere();
    if (!$module->getProperName())
      $module->setProperName($module->getProperName(TRUE));

    $result = $module->save();

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      $content[] = _("An error occurred during registration.");
      $content[] = _("Check your logs for more information.") . "<br />";
    } else {
      $content[] = _("Registration successful.");
      $selfselfResult = $this->registerModToMod($module, $module, $content);
      $otherResult = $this->registerOthersToSelf($module, $content);
      $selfResult = $this->registerSelfToOthers($module, $content);
    }

    $content[] = "<br />";
    return $result;
  }

  function unregisterModule($module, &$content){
    $content[] = _("Unregistering module from core.");

    $db = new PHPWS_DB("modules");
    $db->addWhere("title", $module->getTitle());
    $result = $db->delete();

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      $content[] = _("An error occurred while unregistering.");
      $content[] = _("Check your logs for more information.") . "<br />";
    } else {
      $content[] = _("Unregistering was successful.");
      $selfselfResult = $this->unregisterModToMod($module, $module, $content);
      $otherResult = $this->unregisterOthersToSelf($module, $content);
      $selfResult = $this->unregisterSelfToOthers($module, $content);
      $result = $this->unregisterAll($module);
    }

    $content[] = "<br />";
    return $result;
  }

  function getRegMods(){
    $db = & new PHPWS_DB("modules");
    $db->addWhere("register", 1);
    return $db->loadObjects("PHPWS_Module");
  }

  function setRegistered($module, $registered){
    $db = & new PHPWS_DB("registered");
    $db->addValue("registered", $registered);
    $db->addValue("module", $module);
    $result = $db->insert();
    if (PEAR::isError($result))
      return $result;
    else
      return (bool)$result;
  }

  function unsetRegistered($module, $registered){
    $db = & new PHPWS_DB("registered");
    $db->addWhere("registered", $registered);
    $db->addWhere("module", $module);
    $result = $db->delete();
    if (PEAR::isError($result))
      return $result;
    else
      return (bool)$result;
  }

  function isRegistered($module, $registered){
    $db = & new PHPWS_DB("registered");
    $db->addWhere("registered", $registered);
    $db->addWhere("module", $module);
    $result = $db->select("one");
    if (PEAR::isError($result))
      return $result;
    else
      return (bool)$result;
  }

  function registerModToMod($sourceMod, $regMod, &$content){
    $registerFile = $sourceMod->getDirectory() . "boost/register.php";

    if (!is_file($registerFile))
      return NULL;

    if (PHPWS_Boost::isRegistered($sourceMod->getTitle(), $regMod->getTitle()))
      return NULL;

    include_once($registerFile);

    $registerFunc = $sourceMod->getTitle() . "_register";

    if (!function_exists($registerFunc))
      return NULL;

    $result = $registerFunc($regMod->getTitle(), $content);    

    if (PEAR::isError($result)){
      $content[] = _print(_("An error occurred while registering the [var1] module."), array($regMod->getProperName()));
      $content[] = $result->getMessage();
    } elseif ($result == TRUE){
      PHPWS_Boost::setRegistered($sourceMod->getTitle(), $regMod->getTitle());
      $content[] = _print(_("[var1] successfully registered to [var2]."), array($regMod->getProperName(TRUE), $sourceMod->getProperName(TRUE)));
    }
  }

  function unregisterModToMod($sourceMod, $regMod, &$content){
    $unregisterFile = $sourceMod->getDirectory() . "boost/unregister.php";

    if (!is_file($unregisterFile))
      return NULL;
    
    if (!PHPWS_Boost::isRegistered($sourceMod->getTitle(), $regMod->getTitle()))
      return NULL;

    include_once($unregisterFile);

    $unregisterFunc = $sourceMod->getTitle() . "_unregister";

    if (!function_exists($unregisterFunc))
      return NULL;

    $result = $unregisterFunc($regMod->getTitle(), $content);    

    if (PEAR::isError($result)){
      $content[] = _print(_("An error occurred while unregistering the [var1] module."), array($regMod->getProperName()));
      $content[] = $result->getMessage();
    } elseif ($result == TRUE){
      PHPWS_Boost::unsetRegistered($sourceMod->getTitle(), $regMod->getTitle());
      $content[] = _print(_("[var1] successfully unregistered from [var2]."), array($regMod->getProperName(TRUE), $sourceMod->getProperName(TRUE)));
    }
  }


  function registerSelfToOthers($module, &$content){
    $content[] = _("Registering this module to other modules.");
    
    $modules = PHPWS_Boost::getRegMods();

    if (!is_array($modules))
      return;

    foreach ($modules as $regMod){
      $regMod->init();
      $result = $this->registerModToMod($regMod, $module, $content);
    }
  }

  function unregisterSelfToOthers($module, &$content){
    $content[] = _("Unregistering this module from other modules.");
    
    $modules = PHPWS_Boost::getRegMods();

    if (!is_array($modules))
      return;

    foreach ($modules as $regMod){
      $regMod->init();
      $result = $this->unregisterModToMod($regMod, $module, $content);
    }
  }


  function registerOthersToSelf($module, &$content){
    $content[] = _("Registering other modules to this module.");

    $modules = PHPWS_Boost::getInstalledModules();

    if (!is_array($modules))
      return;

    foreach ($modules as $regMod){
      $regMod->init();
      $result = $this->registerModToMod($module, $regMod, $content);
    }
  }

  function unregisterOthersToSelf($module, &$content){
    $content[] = _("Unregistering other modules from this module.");

    $modules = PHPWS_Boost::getInstalledModules();

    if (!is_array($modules))
      return;

    foreach ($modules as $regMod){
      $regMod->init();
      $result = $this->unregisterModToMod($module, $regMod, $content);
    }
  }

  function unregisterAll($module){
    $db = & new PHPWS_DB("registered");
    $db->addWhere("registered", $module->getTitle());
    return $db->delete();
  }

  function importSQL($file){
    require_once "File.php";

    if (!is_file($file))
      return PHPWS_Error::get(BOOST_ERR_NO_INSTALLSQL, "boost", "importSQL", "File: " . $file);

    $sql = File::readAll($file);
    $result = PHPWS_DB::import($sql);
    return $result;
  }

  function addLog($module, $message){
    $message = _("Module") . " - " . $module . " : " . $message;
    PHPWS_Core::log($message, "boost.log");
  }

  function aboutView($module){
    PHPWS_Core::initCoreClass("Module.php");
    $mod = & new PHPWS_Module($module);
    $file = $mod->getDirectory() . "conf/about.html";

    if (is_file($file))
      include $file;
    else
      echo _("The About file is missing for this module.");
    exit();
  }


}

?>