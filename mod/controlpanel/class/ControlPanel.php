<?php

class PHPWS_ControlPanel {

  function getTabs($frame, $active=NULL, $activeLinkable=TRUE){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $DB = & new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("frame", $frame);
    $DB->addOrder("tab_order");
    $DB->addColumn("id");
    $result = $DB->select("col");

    foreach ($result as $id){
      $tab = & new PHPWS_ControlPanel_Tab($id);

      if (isset($active) && $active == $id)
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
    //$template['LINKS'] = PHPWS_Links::getLinks($currentTab);

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

}

?>