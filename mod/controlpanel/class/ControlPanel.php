<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$ 
 */

PHPWS_Core::initModClass('controlpanel', 'Panel.php');

class PHPWS_ControlPanel {

  function display($content=NULL, $imbed = TRUE){
    Layout::addStyle('controlpanel');

    $panel = new PHPWS_Panel('controlpanel');

    if (1 || !isset($_SESSION['Control_Panel_Tabs'])){
      PHPWS_ControlPanel::loadTabs($panel);
    }
    else {
      $panel->setTabs($_SESSION['Control_Panel_Tabs']);
    }

    $allLinks = PHPWS_ControlPanel::getAllLinks();


    $checkTabs = $panel->getTabs();

    if (empty($checkTabs)){
      PHPWS_Error::log(CP_NO_TABS, 'controlpanel', 'display');
      PHPWS_ControlPanel::makeDefaultTabs();
      PHPWS_ControlPanel::reset();
      PHPWS_Core::errorPage();
      exit();
    } 

    $defaultTabs = PHPWS_ControlPanel::getDefaultTabs();

    foreach ($defaultTabs as $tempTab)
      $tabList[] = $tempTab['id'];

    if (!empty($allLinks)) {
      $links = array_keys($allLinks);
    }

    foreach ($checkTabs as $tab){
      if ($tab->getItemname() == 'controlpanel' &&
	  in_array($tab->getId(), $tabList) &&
	  (!isset($links) || !in_array($tab->getId(), $links))
	  ) {
	$panel->dropTab($tab->id);
      }
    }

    if (empty($panel->tabs)) {
      return _('No tabs available in the Control Panel.');
    }

    if (!isset($content)){
      if (isset($allLinks[$panel->getCurrentTab()])){
	foreach ($allLinks[$panel->getCurrentTab()] as $id => $link)
	  $content[] = $link->view();
	
	$panel->setContent(implode('', $content));
      }
    } else
      $panel->setContent($content);

    if (!isset($panel->tabs[$panel->getCurrentTab()])) {
      return _('An error occurred while accessing the Control Panel.');
    }
    $tab = $panel->tabs[$panel->getCurrentTab()];
    $link = str_replace('&amp;', '&', $tab->getLink(FALSE)) . '&tab=' . $tab->getId();
    $current_link = ereg_replace($_SERVER['PHP_SELF'] . '\?', '', $_SERVER['REQUEST_URI']);

    // Headers to the tab's link if it is not a control panel
    // link tab. 
    if (isset($_REQUEST['command']) &&
	$_REQUEST['command'] == 'panel_view' &&
	!preg_match('/controlpanel/', $link) &&
	$link != $current_link
	){
      PHPWS_Core::reroute($link);
    }


    $_SESSION['Control_Panel_Tabs'] = $panel->getTabs();
    return $panel->display($imbed);
  }

  function loadTabs(&$panel){
    $DB = new PHPWS_DB('controlpanel_tab');
    $DB->addOrder('tab_order');
    $DB->setIndexBy('id');
    $result = $DB->getObjects('PHPWS_Panel_Tab');

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }

    $panel->setTabs($result);
  }

  function getAllTabs(){
    $db = & new PHPWS_DB('controlpanel_tab');
    $db->setIndexBy('id');
    $db->addOrder('tab_order');
    return $db->getObjects('PHPWS_Panel_Tab');
  }

  function getAllLinks(){
    $allLinks = NULL;

    // This session prevents the DB query and link
    // creation from being repeated.
    if (isset($_SESSION['CP_All_links'])) {
      return $_SESSION['CP_All_links'];
    }

    $DB = new PHPWS_DB('controlpanel_link');
    $DB->addOrder('tab');
    $DB->addOrder('link_order');
    $DB->setIndexBy('id');
    $result = $DB->getObjects('PHPWS_Panel_Link');
    
    foreach ($result as $link){
      if (!$link->isRestricted() || Current_User::allow($link->getItemName())) {
	$allLinks[$link->getTab()][] = $link;
      }
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
      $newTab->setItemname('controlpanel');
      $newTab->save();

      if ($tab['id'] == 'unsorted')
	$defaultId = $newTab->getId();
    }

    $db = & new PHPWS_DB('controlpanel_link');
    $result = $db->getObjects('PHPWS_Panel_Link');

    $count = 1;

    foreach ($result as $link){
      $link->setTab($defaultId);
      $link->setLinkOrder($count);
      $link->save();
      $count++;
    }
  }

  function getDefaultTabs(){
    include PHPWS_Core::getConfigFile('controlpanel', 'controlpanel.php');
    return $tabs;
  }

}

?>