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

      /* Check to see if this is an older module and load Crutch if so */
      if ($mod['pre94'] == 1){
	PHPWS_Core::initCoreClass("Crutch.php");
	PHPWS_Crutch::initializeModule($mod['title']);
	$GLOBALS['pre094_modules'][] = $mod['title'];
	$GLOBALS['Modules'][$mod['title']] = $mod;
      }

      /* Using include instead of require to prevent broken mods from hosing the site */
      $includeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/inc/init.php";

      if (is_file($includeFile)){
	include($includeFile);
	$GLOBALS['Modules'][$mod['title']] = $mod;
      }
    }
  }

  function closeModules(){
    if (!isset($GLOBALS['Modules'])){
      PHPWS_Error::log(PHPWS_NO_MODULES, "core", "runtimeModules");
      PHPWS_Core::errorPage();
    }
    
    foreach ($GLOBALS['Modules'] as $mod){
      $includeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/inc/close.php";
      if (is_file($includeFile))
	include($includeFile);
    }

    if (isset($GLOBALS['pre094_modules']))
      PHPWS_Crutch::closeSessions();
  }


  function getModules($active=TRUE){
    $DB = new PHPWS_DB("modules");
    if ($active == TRUE)
      $DB->addWhere("active", 1);
    $DB->addOrder("priority asc");
    return $DB->select();
  }

  function runtimeModules(){
    if (!isset($GLOBALS['Modules'])){
      PHPWS_Error::log(PHPWS_NO_MODULES, "core", "runtimeModules");
      PHPWS_Core::errorPage();
    }

    foreach ($GLOBALS['Modules'] as $title=>$mod){
      if (isset($GLOBALS['pre094_modules']) && 
	  !isset($GLOBALS['Crutch_Session_Started']) && 
	  in_array($title, $GLOBALS['pre094_modules']))
	PHPWS_Crutch::startSessions();
	
      PHPWS_Core::setCurrentModule($title);
      $runtimeFile = PHPWS_SOURCE_DIR . "mod/" . $mod['title'] . "/inc/runtime.php";
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
    if (!PHPWS_Core::isPosted()){
      $_SESSION['PHPWS_LastPost'][] = md5(serialize($_POST));
      if (count($_SESSION['PHPWS_LastPost']) > MAX_POST_TRACK)
	array_shift($_SESSION['PHPWS_LastPost']);
    }
  }

  function isPosted(){
    if (!isset($_SESSION['PHPWS_LastPost']) || !isset($_POST))
      return FALSE;

    return in_array(md5(serialize($_POST)), $_SESSION['PHPWS_LastPost']);
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

    case "request":
      echo phpws_debug::testarray($_REQUEST);
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


    if (!is_file($file) || FORCE_MOD_CONFIG){
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

    if (get_class($log) == "log_file"){
      $log->log($message, PEAR_LOG_NOTICE);
      $log->close();
    }

  }

  function errorPage(){
    include "config/core/error_page.html";
    exit();
  }

  function isWindows(){
    if (isset($_SERVER['WINDIR']) || preg_match("/(microsoft|win32)/i", $_SERVER['SERVER_SOFTWARE']))
      return TRUE;
    else
      return FALSE;
  }

  function checkSecurity(){
    if (CHECK_DIRECTORY_PERMISSIONS == TRUE && !isset($_SESSION['SECURE'])){
      if (is_writable("./config/") || is_writable("./templates/")){
	PHPWS_Error::log(PHPWS_DIR_NOT_SECURE, "core");
	PHPWS_Core::errorPage();
      }
    }
  }

  function coreModList(){
    $file = PHPWS_Core::getConfigFile("core", "core_modules.php");
    if (PEAR::isError($file))
      return $file;

    include $file;
    return $core_modules;
  }

  function installModList(){
    $db = & new PHPWS_DB("modules");
    $db->addColumn("title");
    return $db->select("col");
  }

}// End of core class

?>