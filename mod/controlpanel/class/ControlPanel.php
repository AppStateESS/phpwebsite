<?php

class PHPWS_ControlPanel {

  function getTabs($frame, $active=NULL, $activeLinkable=TRUE, $only=NULL){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $DB = & new PHPWS_DB("controlpanel_tab");

    if (isset($only) && is_array($only)){
      foreach ($only as $tabId)
	$DB->addWhere("id", $tabId, NULL, "or");
    }

    $DB->addWhere("frame", $frame);
    $DB->addOrder("tab_order");
    $result = $DB->loadItems("PHPWS_ControlPanel_Tab");

    echo $DB->lastQuery();

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

    $links = PHPWS_ControlPanel::getAllLinks($currentTab);

    $result = PHPWS_ControlPanel::getTabs('controlpanel', $_SESSION['ControlPanel_Current_Tab'], FALSE, array_keys($links));
    
    $template['TABS'] = implode("", $result);

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

  function getAllLinks(){
    $DB = new PHPWS_DB("controlpanel_link");
    $DB->addOrder("tab");
    $DB->addOrder("link_order");
    $result = $DB->loadItems("PHPWS_ControlPanel_Link");
    foreach ($result as $link){
      if ($_SESSION['User']->allow($link->getModule(), $link->getItemName()))
	$allLinks[$link->getTab()][] = $link;
    }

    return $allLinks;
  }

}

?>