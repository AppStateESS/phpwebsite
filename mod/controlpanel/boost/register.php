<?php

function controlpanel_register($module, &$content){
  PHPWS_Core::initModClass("controlpanel", "Tab.php");
  PHPWS_Core::initModClass("controlpanel", "Link.php");
  $cpFile = PHPWS_Core::getConfigFile($module, "controlpanel.php");

  if (PEAR::isError($cpFile)){
    PHPWS_Boost::addLog("controlpanel", $cpFile->getUserInfo());
    return NULL;
  }

  include_once($cpFile);

  if (isset($tabs) && is_array($tabs)){
    foreach ($tabs as $info){
      $tab = new PHPWS_Panel_Tab;
      $tab->setTitle($info['title']);
      $tab->setLink($info['link']);
      $tab->setLabel($info['label']);

      if (isset($info['itemname']))
	$tab->setItemname($info['itemname']);
      else
	$tab->setItemname($module);

      $tab->save();
    }
    $content[] = _print(_("Control Panel tabs created for [var1]."), $module);
  }

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
      elseif (!isset($result))
	exit("problem");

      $modlink->setTab($result['id']);
      $result = $modlink->save();
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	$content[] = _("There was a problem trying to save a Control Panel link.");
	return FALSE;
      }
      $db->resetWhere();
    }
    PHPWS_ControlPanel::reset();
    $content[] = _print(_("Control Panel links created for [var1]."), $module);
  }


  return TRUE;
}

?>