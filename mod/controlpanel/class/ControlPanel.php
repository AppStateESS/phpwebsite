<?php

class PHPWS_ControlPanel {

  function display($content=NULL, $imbed = TRUE){
    Layout::addStyle("controlpanel");
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $panel = new PHPWS_Panel('controlpanel');

    if (!isset($_SESSION['Control_Panel_Tabs']))
      $panel->loadTabs();
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

    $tabs = array_keys($checkTabs);
    $links = array_keys($allLinks);

    foreach ($tabs as $tabId)
      if (!in_array($tabId, $links))
	$panel->dropTab($tabId);

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


  function getAllLinks(){
    $allLinks = NULL;

    if (isset($_SESSION['CP_All_links']))
      return $_SESSION['CP_All_links'];

    $DB = new PHPWS_DB("controlpanel_link");
    $DB->addOrder("tab");
    $DB->addOrder("link_order");
    $DB->setIndexBy("id");
    $result = $DB->loadObjects("PHPWS_Panel_Link");

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
    include PHPWS_Core::getConfigFile("controlpanel", "controlpanel.php");
    
    foreach ($tabs as $tab){
      $newTab = & new PHPWS_Panel_Tab;
      $newTab->setTitle($tab['title']);
      $newTab->setLabel($tab['label']);
      $newTab->setLink($tab['link']);
      $newTab->setItemname("controlpanel");
      $newTab->save();

      if ($tab['label'] == "unsorted")
	$defaultId = $newTab->getId();
    }

    $db = & new PHPWS_DB("controlpanel_link");
    $result = $db->loadObjects("PHPWS_Panel_Link");

    $count = 1;

    foreach ($result as $link){
      $link->setTab($defaultId);
      $link->setLinkOrder($count);
      $link->save();
      $count++;
    }

  }

}

?>