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

    $module = preg_replace("/\W/", "", $_REQUEST['helpMod']);
    $help = preg_replace("/\W/", "", $_REQUEST['option']);
    $file = PHPWS_Core::getConfigFile($module, "help.". DEFAULT_LANGUAGE . ".php");
    if (PEAR::isError($file)){
      PHPWS_Error::log($file);
      return NULL;
    }
    
    include $file;

    if (!isset($$help)){
      PHPWS_Error::log(PHPWS_UNMATCHED_OPTION, "core", "PHPWS_Help::show_link", "Option: $help");
      return NULL;
    }
    Layout::addStyle("help");
    Layout::alternateTheme($$help, "help", "help.tpl");
  }

}

?>