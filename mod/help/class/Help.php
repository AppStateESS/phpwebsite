<?php

$config = PHPWS_Core::getConfigFile("help", "config.php");

if (PEAR::isError($config)){
  PHPWS_Error::log($config);
} else {
  include_once $config;
} 

class PHPWS_Help{

  function show_link($module, $help, $label=NULL){
    Layout::addStyle("help");
    if (!isset($label))
      $label = DEFAULT_HELP_LABEL;

    $vars['label'] = $label;
    $vars['address'] = "index.php?module=help&amp;helpMod=$module&amp;option=$help";
    $link = Layout::getJavascript("open_window", $vars);
    $result = PHPWS_Template::process(array("LINK"=> $link), "help", "link.tpl");
    return $result;
  }

  function show_help(){
    if (!isset($_REQUEST['helpMod'])){
      echo "help page information here";
      exit();
    }

    $module = preg_replace("/[^\w]+/", "", $_REQUEST['helpMod']);
    $help = preg_replace("/[^\w\-]+/", "", $_REQUEST['option']);
    $filename = "mod/$module/conf/help.". CURRENT_LANGUAGE . ".php";
    $default = "mod/$module/conf/help.php";
    if (!is_file($filename)){
      if (!is_file($default)){
	PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, "core", "show_help", $default);
	exit(_("The help file for this module is missing."));
      } else
	include $default;
    } else
      include $filename;

    if (!isset($$help)){
      PHPWS_Error::log(PHPWS_UNMATCHED_OPTION, "core", "PHPWS_Help::show_link", "Option: $help");
      exit(_("No help exists for this topic."));
      return NULL;
    }
    Layout::addStyle("help");
    Layout::alternateTheme($$help, "help", "help.tpl");
  }

}

?>