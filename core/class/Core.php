<?php

/**
 * Controls module manipulation
 *
 * Loads modules and their respective files.
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */


class PHPWS_Core {

  function initializeModules(){
    if (!$moduleList = PHPWS_Core::getModules()){
      PHPWS_Error::log(PHPWS_NO_MODULES, "core", "initializeModules");
      PHPWS_Core::errorPage();
    }

    if (PEAR::isError($moduleList)){
      PHPWS_Error::log($moduleList);
      PHPWS_Core::errorPage();
    }
      
    foreach ($moduleList as $mod){
      PHPWS_Core::setCurrentModule($mod['title']);
      /* Using include instead of require to prevent broken mods from hosing the site */
      $includeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/init.php";

      if (is_file($includeFile)){
	include($includeFile);
	$GLOBALS['Modules'][$mod['title']] = $mod;
      }
    }
  }

  function closeModules(){
    if (!isset($GLOBALS['Modules']))
      die("No modules are active");
    
    foreach ($GLOBALS['Modules'] as $mod){
      $includeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/close.php";
      if (is_file($includeFile))
	include($includeFile);
    }
  }


  function getModules($active=TRUE){
    $DB = new PHPWS_DB("modules");
    $DB->addWhere("active", 1);
    $DB->addOrder("priority asc");
    return $DB->select();
  }

  function runtimeModules(){
    if (!isset($GLOBALS['Modules']))
      die("Core was unable to locate modules");

    foreach ($GLOBALS['Modules'] as $title=>$mod){
      PHPWS_Core::setCurrentModule($title);
      $runtimeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/runtime.php";
      is_file($runtimeFile) ? include_once $runtimeFile : NULL;
    }

  }


  function runCurrentModule(){
    if (isset($_REQUEST['module'])){

      PHPWS_Core::setCurrentModule($_REQUEST['module']);
      $modFile = PHPWS_SOURCE_DIR . "mod/" . $_REQUEST['module'] . "/index.php";

      if (is_file($modFile))
	include $modFile;
    }
  }


  function initModClass($module, $file){
    $classFile = PHPWS_SOURCE_DIR . "mod/" . $module . "/class/" . $file;
    if (is_file($classFile)){
      require_once $classFile;
      return TRUE;
    }
    else {
      PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, "core", "initModClass", "File: $classFile");
      return FALSE;
    }
  }

  function initCoreClass($file){
    $classFile = PHPWS_SOURCE_DIR . "core/class/" . $file;
    if (is_file($classFile)){
      require_once $classFile;
      return TRUE;
    }
    else {
      PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, "core", "initCoreClass", "File: $classFile");
      return FALSE;
    }
  }

  function setLastPost(){
    if (isset($_POST))
      $_SESSION['PHPWS_LastPost'] = $_POST;
    else
      $_SESSION['PHPWS_LastPost'] = array();
  }

  function isLastPost($postVar=NULL){
    if ($_POST == PHPWS_Core::getLastPost())
      return TRUE;
    return FALSE;
  }
 
  function getLastPost(){
    if (!isset($_SESSION['PHPWS_LastPost']))
      return FALSE;

    return $_SESSION['PHPWS_LastPost'];
  }

  function home(){
    header("location:./");
    exit();
  }

  function killSession($sess_name){
    $_SESSION[$sess_name] = NULL;
    unset($_SESSION[$sess_name]);
  }

  function killAllSessions(){
    $_SESSION = array();
    unset($_SESSION);
    session_destroy();
  }// END FUNC killAllSessions()

  function moduleExists($module){
    return isset($GLOBALS['Modules'][$module]);
  }

  function getCurrentModule(){
    return $GLOBALS['PHPWS_Current_Mod'];
  }

  function setCurrentModule($module){
    $GLOBALS['PHPWS_Current_Mod'] = $module;
  }

  function report(){
    if (!isset($_GET['report']))
      return NULL;

    switch ($_GET['report']){
    case "post":
      echo phpws_debug::testarray($_POST);
      break;

    case "session":
      if (!isset($_GET['session']))
	return NULL;

      $sessionName = &$_GET['session'];
      $session = $_SESSION[$sessionName];

      if (is_object($session))
	echo phpws_debug::testobject($session);
      elseif (is_array($session))
	echo phpws_debug::testarray($session);
      else
	echo
	  $session;
      break;
    }
  }

  function getConfigFile($module, $file){
    if ($module == "core"){
      $altfile = PHPWS_SOURCE_DIR . "config/core/$file";
      $file = "./config/core/$file";
    }
    else {
      $altfile = PHPWS_SOURCE_DIR . "mod/$module/conf/$file";
      $file = "config/$module/$file";
    }


    if (!is_file($file)){
      if (!is_file($altfile))
	return PHPWS_Error::get(PHPWS_FILE_NOT_FOUND, "core", "getConfigFile", "file = $file");
      else
	$file = $altfile;
    }

    return $file;
  }

  function &loadAsMod(){
    PHPWS_Core::initCoreClass("Module.php");
    
    $core = & new PHPWS_Module;
    $core->setTitle("core");
    $core->setDirectory(PHPWS_SOURCE_DIR . "core/");
    $file = PHPWS_Core::getConfigFile("core", "version.php");
    if (PEAR::isError($file))
      return $file;
    else
      include $file;

    $core->setVersion($version);
    $core->setRegister(FALSE);
    $core->setImportSQL(TRUE);
    $core->setProperName("Core");

    return $core;
  }

  function log($message, $filename, $type=NULL){
    require_once "Log.php";

    if (!is_writable(PHPWS_LOG_DIRECTORY))
      exit("Unable to write to log directory " . PHPWS_LOG_DIRECTORY);

    $conf = array('mode' => LOG_PERMISSION, 'timeFormat' => LOG_TIME_FORMAT);
    $log  = &Log::singleton('file', PHPWS_LOG_DIRECTORY . $filename, $type, $conf, PEAR_LOG_NOTICE);

    $log->log($message, PEAR_LOG_NOTICE);

    $log->close();

  }

  function errorPage(){
    include "config/core/error_page.html";
    exit();
  }

}// End of core class


?>