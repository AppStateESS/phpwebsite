<?php

class Boost_Action {

  function checkupdate($mod_title){
    PHPWS_Core::initCoreClass("Module.php");
    $module = & new PHPWS_Module($mod_title);

    $file = $module->getVersionHttp();
    $valueArray = @file($file);

    if (!isset($valueArray) || !stristr($valueArray[0], "version"))
      return _("Update file not found.");

    foreach ($valueArray as $values){
      list($key, $value) = explode("=", preg_replace("/\s/", "", $values));
      $versionInfo[$key] = $value;
    }
    
    $template['LOCAL_VERSION'] = _("Local Version:") . " " . $module->getVersion();
    $template['STABLE_VERSION'] = _("Current Stable Version:") . " " . $versionInfo['version'];

    if ($versionInfo['version'] > $module->getVersion()){
      $template['STATUS'] = _("An update is available.") . "<br />";
      $template['UPDATE_PATH_LABEL'] = _("Download here");
      $template['UPDATE_PATH'] = "<a href=\"" . $versionInfo['path'] . $versionInfo['filename'] . "\">" . $versionInfo['path'] . $versionInfo['filename'] . "</a>";
    }
    else {
      $template['STATUS'] = _("No update required.");
    }

    $template['TITLE'] = _("Module") . ": " . $module->getProperName(TRUE);

    return PHPWS_Template::process($template, "boost", "check_update.tpl");
  }

  function installModule($module_title){
    PHPWS_Core::initModClass("boost", "Boost.php");
    
    $boost = new PHPWS_Boost;
    $boost->loadModules(array($module_title));
    return $boost->install();
    /*
    if ($_SESSION['Boost']->isFinished())
      return TRUE;
    else
      return FALSE;
    */
  }

  function uninstallModule($module_title){
    PHPWS_Core::initModClass("boost", "Boost.php");
    
    $boost = new PHPWS_Boost;
    $boost->loadModules(array($module_title));

    $content = $boost->uninstall();

    return $content;
  }

  function updateModule($module_title){
    PHPWS_Core::initModClass("boost", "Boost.php");
    $boost = new PHPWS_Boost;
    $boost->loadModules(array($module_title), FALSE);
    $content = $boost->update();
    return $content;
  }
}

?>