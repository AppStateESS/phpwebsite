<?php

function controlpanel_register($module, &$content){
  PHPWS_Core::initModClass("controlpanel", "Tab.php");
  PHPWS_Core::initModClass("controlpanel", "Link.php");
  PHPWS_Core::initModClass("controlpanel", "ControlPanel.php");
  $cpFile = PHPWS_Core::getConfigFile($module, "controlpanel.php");

  if (PEAR::isError($cpFile)){
    PHPWS_Boost::addLog("controlpanel", $cpFile->getUserInfo());
    return NULL;
  }

  include_once($cpFile);

  if (isset($tabs) && is_array($tabs)){
    foreach ($tabs as $info){
      $tab = new PHPWS_Panel_Tab;
      if (!isset($info['title'])){
	$content[] = _("Unable to create tab.") . " " . _("Missing title.");
	continue;
      }	
      $tab->setTitle($info['title']);

      if (!isset($info['link'])){
	$content[] = _("Unable to create tab.") . " " . _("Missing link.");
	continue;
      }	

      $tab->setLink($info['link']);

      if (!isset($info['label']))
	$tab->setLabel(strtolower(preg_replace("/\W/", "_", $info['title'])));
      else
	$tab->setLabel($info['label']);

      if (isset($info['itemname']))
	$tab->setItemname($info['itemname']);
      else
	$tab->setItemname("controlpanel");

      $tab->save();
    }
    $content[] = _print(_("Control Panel tabs created for [var1]."), $module);
  } else
    PHPWS_Boost::addLog($module, _("No Control Panel tabs found."));
    

  if (isset($link) && is_array($link)){
    $db = new PHPWS_DB("controlpanel_tab");
    foreach ($link as $info){
      $modlink = new PHPWS_Panel_Link;

      if (isset($info['label']))
	$modlink->setLabel($info['label']);

      if (isset($info['restricted']))
	$modlink->setRestricted($info['restricted']);
      elseif (isset($info['admin']))
	$modlink->setRestricted($info['admin']);

      $modlink->setUrl($info['url']);
      $modlink->setActive(1);

      if (isset($info['itemname']))
	$modlink->setItemName($info['itemname']);
      else
	$modlink->setItemName($module);

      $modlink->setDescription($info['description']);
      if (is_string($info['image']))
	$modlink->setImage("images/mod/$module/" . $info['image']);
      elseif(is_array($info['image']))
	$modlink->setImage("images/mod/$module/" . $info['image']['name']);

      $db->addWhere("label", $info['tab']);
      $result = $db->select("row");
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	continue;
      }
      elseif (!isset($result)){
	$db->reset();
	$db->addWhere("label", "unsorted");
	$db->addColumn("id");
	$tab_id = $db->select("one");
	PHPWS_Boost::addLog($module, _("Unable to load a link into a specified tab."));
      } else
	$tab_id = $result['id'];

      $modlink->setTab($tab_id);
      $result = $modlink->save();
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	$content[] = _("There was a problem trying to save a Control Panel link.");
	return FALSE;
      }
      $db->resetWhere();
    }
    $content[] = _print(_("Control Panel links created for [var1]."), $module);
  } else
    PHPWS_Boost::addLog($module, _("No Control Panel links found."));

  PHPWS_ControlPanel::reset();
  return TRUE;
}

?>