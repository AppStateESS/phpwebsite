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

    if (!isset($content)){
      $allLinks = PHPWS_ControlPanel::getAllLinks();

      if (!$allLinks)
	return _("Control Panel does not contain any links.");

      $tabs = array_keys($panel->getTabs());
      $links = array_keys($allLinks);

      foreach ($tabs as $tabId)
	if (!in_array($tabId, $links))
	  $panel->dropTab($tabId);

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
    $result = $DB->loadObjects("PHPWS_Panel_Link", 'id');

    foreach ($result as $link){
      if (!$link->isRestricted() || $_SESSION['User']->allow($link->getItemName()))
	$allLinks[$link->getTab()][] = $link;
    }
    
    $_SESSION['CP_All_links'] = $allLinks;
    return $_SESSION['CP_All_links'];
  }

}

?>