<?php

function register($module, &$content){
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
  }

  if (isset($links) && is_array($links)){
    $db = new PHPWS_DB("controlpanel_tab");
    foreach ($links as $info){
      $link = new PHPWS_Panel_Link;
      $link->setLabel($info['label']);
      $link->setRestricted($info['restricted']);
      $link->setUrl($info['url']);
      $link->setActive(1);

      if (isset($info['itemname']))
	$link->setItemName($info['itemname']);
      else
	$link->setItemName($module);

      $link->setDescription($info['description']);
      $link->setImage("images/mod/$module/" . $info['image']);
      $db->addWhere("label", $info['tab']);
      $result = $db->select("row");
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	continue;
      }
      elseif (!isset($result))
	exit("problem");

      $link->setTab($result['id']);
      $result = $link->save();
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	$content[] = _("There was a problem trying to save a Control Panel link.");
	return FALSE;
      }
      $db->resetWhere();
    }
  }


  return TRUE;
}

?>