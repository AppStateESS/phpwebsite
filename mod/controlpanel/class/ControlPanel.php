<?php

class PHPWS_ControlPanel {

  function getTabs($itemname, $active=NULL, $activeLinkable=TRUE, $only=NULL){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $DB = & new PHPWS_DB("controlpanel_tab");

    if (isset($only) && is_array($only)){
      foreach ($only as $tabId)
	$DB->addWhere("id", $tabId, NULL, "or");
    }

    $DB->addWhere("itemname", $itemname);
    $DB->addOrder("tab_order");

    $result = $DB->loadItems("PHPWS_ControlPanel_Tab");

    foreach ($result as $tab){
      if (isset($active[$itemname]) && $active[$itemname] == $tab->getId())
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

    $currentTab = PHPWS_ControlPanel::getCurrentTab("controlpanel");

    $links = PHPWS_ControlPanel::getAllLinks();

    $template['LINKS'] = PHPWS_ControlPanel::buildPanel($links[$currentTab]);

    $result = PHPWS_ControlPanel::getTabs('controlpanel', $_SESSION['ControlPanel_Current_Tab'], TRUE, array_keys($links));
    if (PEAR::isError($result))
      echo $result->getMessage();


    $template['TABS'] = implode("", $result);

    return PHPWS_Template::process($template, "controlpanel", "panel.tpl");
  }

  function getCurrentTab($itemName){
    if (isset($_SESSION['ControlPanel_Current_Tab'][$itemName]))
      return $_SESSION['ControlPanel_Current_Tab'][$itemName];
    else {
      $currentTab = PHPWS_ControlPanel::getFirstTab($itemName);
      PHPWS_ControlPanel::setCurrentTab($itemName, $currentTab);       
      return $currentTab;
    }
  }

  function buildPanel($links){

    foreach ($links as $link){
      $tpl['IMAGE'] = $link->getImage(TRUE, TRUE);
      $tpl['URL']   = $link->getUrl();
      $tpl['NAME']  = $link->getLabel();
      $tpl['DESCRIPTION'] = $link->getDescription();
      $final[] = PHPWS_Template::process($tpl, "controlpanel", "link.tpl");
    }

    return implode("", $final);

  }

  function setCurrentTab($itemName, $tab){
    $_SESSION['ControlPanel_Current_Tab'][$itemName] = $tab;
  }

  function getFirstTab($itemName){
    $DB = & new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("itemname", $itemName);
    $DB->addColumn("tab_order");
    $result = $DB->select("min");

    return $result;
  }

  function getAllLinks(){
    if (isset($_SESSION['CP_All_links']))
      return $_SESSION['CP_All_links'];

    $DB = new PHPWS_DB("controlpanel_link");
    $DB->addOrder("tab");
    $DB->addOrder("link_order");
    $result = $DB->loadItems("PHPWS_ControlPanel_Link");
    foreach ($result as $link){
      if (!$link->isRestricted() || $_SESSION['User']->allow($link->getItemName()))
	$allLinks[$link->getTab()][] = $link;
    }
    
    $_SESSION['CP_All_links'] = $allLinks;
    return $_SESSION['CP_All_links'];
  }

}

?>