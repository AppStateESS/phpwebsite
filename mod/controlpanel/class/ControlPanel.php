<?php

class PHPWS_ControlPanel {

  function getTabs($frame, $active=NULL, $activeLinkable=TRUE){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $DB = & new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("frame", $frame);
    $DB->addOrder("tab_order");
    $result = $DB->loadItems("PHPWS_ControlPanel_Tab");

    foreach ($result as $tab){
      if (isset($active) && $active == $tab->getId())
	$result = $tab->view(TRUE, $activeLinkable);
      else
	$result = $tab->view(FALSE);

      if (PEAR::isError($result))
	return PEAR::raiseError("Unable to getTabs.<br /><b>Reason:</b>" . $result->getMessage());
      else
	$view[] = $result;
    }

    return $view;
  }

  function display(){
    $currentTab = PHPWS_ControlPanel::getCurrentTab();

    $result = PHPWS_ControlPanel::getTabs('controlpanel', $_SESSION['ControlPanel_Current_Tab'], FALSE);
    
    $template['TABS'] = implode("", $result);

    $links = PHPWS_ControlPanel::getLinks();

    return PHPWS_Template::process($template, "controlpanel", "panel.tpl");
  }

  function getCurrentTab(){
    if (isset($_SESSION['ControlPanel_Current_Tab']))
      return $_SESSION['ControlPanel_Current_Tab'];
    else {
      $currentTab = PHPWS_ControlPanel::getFirstTab("controlpanel");
      PHPWS_ControlPanel::setCurrentTab($currentTab);       
      return $currentTab;
    }
  }

  function setCurrentTab($tab){
    $_SESSION['ControlPanel_Current_Tab'] = $tab;
  }

  function getFirstTab($frame){
    $DB = & new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("frame", $frame);
    $DB->addColumn("tab_order");
    $result = $DB->select("min");

    return $result;
  }

  function getLinks($tab=NULL){
    
    if (!isset($_SESSION['CP_Links'])){
      $DB = new PHPWS_DB("controlpanel_link");
      if (isset($tab))
	$DB->addWhere("tab", $tab);
      $result = $DB->loadItems("PHPWS_ControlPanel_Link");
      $_SESSION['CP_Links'] = $result;
      foreach ($result as $row=>$item){
	if (

      }

    }
      echo phpws_debug::testarray($_SESSION['CP_Links']);
  }

}

?>