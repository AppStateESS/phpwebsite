<?php

class PHPWS_ControlPanel {

  function display(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $panel = new PHPWS_Panel('controlpanel');
    $panel->loadTabs();

    $allLinks = PHPWS_ControlPanel::getAllLinks();
    foreach ($allLinks[$panel->getCurrentTab()] as $id => $link)
	$content[] = $link->view();
  

    $panel->setContent(implode("", $content));
    return $panel->display();
  }


  function getAllLinks(){
    if (isset($_SESSION['CP_All_links']))
      return $_SESSION['CP_All_links'];

    $DB = new PHPWS_DB("controlpanel_link");
    $DB->addOrder("tab");
    $DB->addOrder("link_order");
    $result = $DB->loadItems("PHPWS_Panel_Link");
    foreach ($result as $link){
      if (!$link->isRestricted() || $_SESSION['User']->allow($link->getItemName()))
	$allLinks[$link->getTab()][] = $link;
    }
    
    $_SESSION['CP_All_links'] = $allLinks;
    return $_SESSION['CP_All_links'];
  }

}

?>