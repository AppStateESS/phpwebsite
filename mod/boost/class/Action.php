<?php

class Boost_Action {

  function checkupdate($mod_title){
    PHPWS_Core::initCoreClass("Module.php");
    $module = & new PHPWS_Module($mod_title);

    $file = $module->getVersionHttp();
    $valueArray = file($file);

    foreach ($valueArray as $values){
      list($key, $value) = explode("=", preg_replace("/\s/", "", $values));
      $versionInfo[$key] = $value;
    }
    
    $template['LOCAL_VERSION'] = _("Local Version:") . " " . $module->getVersion();
    $template['STABLE_VERSION'] = _("Current Stable Version:") . " " . $versionInfo['version'];

    if ($versionInfo['version'] > $module->getVersion()){
      $template['STATUS'] = _("An update is available.") . "<br />";
      $template['UPGRADE_PATH_LABEL'] = _("Download here");
      $template['UPGRADE_PATH'] = "<a href=\"" . $versionInfo['path'] . $versionInfo['filename'] . "\">" . $versionInfo['path'] . $versionInfo['filename'] . "</a>";
    }
    else {
      $template['STATUS'] = _("No update required.");
    }

    $template['TITLE'] = _("Module") . ": " . $module->getProperName(TRUE);

    return PHPWS_Template::process($template, "boost", "check_update.tpl");
  }

}

?>