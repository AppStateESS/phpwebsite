<?php

class Boost_Form {

  function boostTab(&$panel){
    if (!isset($_REQUEST['tab']))
      return $panel->getCurrentTab();
    else
      return $_REQUEST['tab'];
  }

  function setTabs(&$panel){
    $link = _("index.php?module=boost&amp;action=admin");
    
    $core_links['title'] = _("Core Modules");
    $other_links['title'] = _("Other Modules");

    $other_links['link'] = $core_links['link']  = $link;

    $tabs['core_mods'] = $core_links;
    $tabs['other_mods'] = $other_links;

    $panel->quickSetTabs($tabs);
  }

  function listModules($type){
    Layout::addStyle("boost");
    PHPWS_Core::initCoreClass("Module.php");
    PHPWS_Core::initCoreClass("Text.php");
    PHPWS_Core::initCoreClass("File.php");

    $core_mods      = PHPWS_Core::coreModList();
    $dir_mods       = PHPWS_File::readDirectory(PHPWS_SOURCE_DIR . "mod/", TRUE);
    $installed_mods = PHPWS_Core::installModList();

    foreach ($core_mods as $core_title){
      unset($dir_mods[array_search($core_title, $dir_mods)]);
    }

    if ($type == "core_mods"){
      $allowUninstall = FALSE;
      $modList = $core_mods;
    }
    else {
      $allowUninstall = TRUE;
      $modList = $dir_mods;
    }

    $tpl = new PHPWS_Template("boost");
    $tpl->setFile("module_list.tpl");

    $tpl->setCurrentBlock("mod-row");
    $count = 0;
    foreach ($modList as $title){
      $template = $link_command = NULL;
      $link_command['opmod'] = $title;
      $mod = & new PHPWS_Module($title);

      $proper_name = $mod->getProperName();
      if (!isset($proper_name))
	$proper_name = $title;

      $template['TITLE'] = $proper_name;
      $template['ROW'] = $count % 2;
      if (!$mod->isInstalled()){
	$link_title = _("Install");
	$link_command['action'] = "install";
      } else {
	if ($type != "core_mods"){
	  $uninstallVars = array('opmod'=>$title, "action"=>'uninstall');
	  $template['UNINSTALL'] = PHPWS_Text::moduleLink(_("Uninstall"), "boost", $uninstallVars);
	}
	if ($mod->needsUpgrade()){
	  $link_title = _("Upgrade");
	  $link_command['action'] = "upgrade";
	}
	else {
	  $version_check = $mod->getVersionHttp();
	  
	  if (isset($version_check)){
	    $link_title = _("Check");
	    $link_command['action'] = "check";
	  } else
	    $link_title = _("No Action");
	}
      }

      if ($mod->isAbout()){
	$address = "index.php?module=boost&amp;action=aboutView&amp;aboutmod=" . $mod->getTitle();
	$aboutView = array("label"=>_("About"), "address"=>$address);
	$template['ABOUT'] = Layout::getJavascript("open_window", $aboutView);
      }

      if (isset($link_command['action'])){
	$template['COMMAND'] = PHPWS_Text::moduleLink($link_title, "boost", $link_command);
      } else
	$template['COMMAND'] = $link_title;
      
      $tpl->setData($template);
      $tpl->parseCurrentBlock();
      $count++;
    }

   
   $result = $tpl->get();
   return $result;
    
  }


}

?>