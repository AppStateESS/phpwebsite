<?php

PHPWS_Core::initModClass("controlpanel", "Panel.php");

class PHPWS_ControlPanel {

  function display($content=NULL, $imbed = TRUE){
    Layout::addStyle("controlpanel");

    $panel = new PHPWS_Panel('controlpanel');

    if (!isset($_SESSION['Control_Panel_Tabs']))
      PHPWS_ControlPanel::loadTabs($panel);
    else
      $panel->setTabs($_SESSION['Control_Panel_Tabs']);

    $allLinks = PHPWS_ControlPanel::getAllLinks();

    if (!$allLinks)
      return _("Control Panel does not contain any links.");

    $checkTabs = $panel->getTabs();

    if (empty($checkTabs)){
      PHPWS_Error::log(CP_NO_TABS, "controlpanel", "display");
      PHPWS_ControlPanel::makeDefaultTabs();
      PHPWS_ControlPanel::reset();
      PHPWS_Core::errorPage();
      exit();
    } 

    $links = array_keys($allLinks);
    
    $defaultTabs = PHPWS_ControlPanel::getDefaultTabs();
    foreach ($defaultTabs as $tempTab)
      $tabList[] = $tempTab['id'];

    foreach ($checkTabs as $tab){
      if ($tab->getItemname() == "controlpanel" &&
	  in_array($tab->getId(), $tabList) &&
	  !in_array($tab->getId(), $links)){
	$panel->dropTab($tab->id);
      }
    }
    
    if (!isset($content)){
      if (isset($allLinks[$panel->getCurrentTab()])){
	foreach ($allLinks[$panel->getCurrentTab()] as $id => $link)
	  $content[] = $link->view();
	
	$panel->setContent(implode("", $content));
      }
    } else
      $panel->setContent($content);

    $_SESSION['Control_Panel_Tabs'] = $panel->getTabs();
    return $panel->display($imbed);
  }

  function loadTabs(&$panel){
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addOrder("tab_order");
    $DB->setIndexBy("id");
    $result = $DB->getObjects("PHPWS_Panel_Tab");

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }

    $panel->setTabs($result);
  }

  function getAllLinks(){
    $allLinks = NULL;

    if (isset($_SESSION['CP_All_links']))
      return $_SESSION['CP_All_links'];

    $DB = new PHPWS_DB("controlpanel_link");
    $DB->addOrder("tab");
    $DB->addOrder("link_order");
    $DB->setIndexBy("id");
    $result = $DB->getObjects("PHPWS_Panel_Link");

    foreach ($result as $link){
      if (!$link->isRestricted() || $_SESSION['User']->allow($link->getItemName()))
	$allLinks[$link->getTab()][] = $link;
    }
    
    $_SESSION['CP_All_links'] = $allLinks;
    return $_SESSION['CP_All_links'];
  }

  function reset(){
    unset($_SESSION['Control_Panel_Tabs']);
    unset($_SESSION['CP_All_links']);
  }

  function makeDefaultTabs(){
    $tabs = PHPWS_ControlPanel::getDefaultTabs();

    foreach ($tabs as $tab){
      $newTab = & new PHPWS_Panel_Tab;
      $newTab->setId($tab['id']);
      $newTab->setTitle($tab['title']);
      $newTab->setLink($tab['link']);
      $newTab->setItemname("controlpanel");
      $newTab->save();

      if ($tab['id'] == "unsorted")
	$defaultId = $newTab->getId();
    }

    $db = & new PHPWS_DB("controlpanel_link");
    $result = $db->getObjects("PHPWS_Panel_Link");

    $count = 1;

    foreach ($result as $link){
      $link->setTab($defaultId);
      $link->setLinkOrder($count);
      $link->save();
      $count++;
    }
  }

  function getDefaultTabs(){
    include PHPWS_Core::getConfigFile("controlpanel", "controlpanel.php");
    return $tabs;
  }

}

?>