<?php
include_once PHPWS_SOURCE_DIR . "mod/controlpanel/conf/config.php";

class PHPWS_Panel{
  var $_itemname = NULL;
  var $_tabs     = NULL;
  var $_content  = NULL;
  var $_module   = NULL;
  var $_panel    = NULL;

  function PHPWS_Panel($itemname=NULL){
    if (isset($itemname))
      $this->setItemname($itemname);
  }

  function quickSetTabs($tabs){
    $count = 1;
    foreach ($tabs as $id=>$info){
      $tab = new PHPWS_Panel_Tab;
      $tab->setId($id);
      $tab->setTitle($info['title']);
      $tab->setLink($info['link']);
      $tab->setOrder($count);
      $count++;
      $this->_tabs[$id] = $tab;
    }

  }

  
  function loadTabs(){
    $itemname = $this->getItemname();
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("itemname", $itemname);
    $DB->addOrder("tab_order");
    $result = $DB->loadItems("PHPWS_Panel_Tab", "id");
    if (PEAR::isError($result))
      exit("ERROR in loadTabs");

    $this->setTabs($result);
  }

  function setTabs($tabs){
    $this->_tabs = $tabs;
  }

  function getTabs(){
    return $this->_tabs;
  }

  function setContent($content){
    $this->_content = $content;
  }

  function getContent(){
    return $this->_content;
  }

  function setItemname($itemname){
    $this->_itemname = $itemname;
  }

  function getItemname(){
    return $this->_itemname;
  }


  function setModule($module){
    $this->_module($module);
  }

  function getModule(){
    return $this->_module;
  }

  function setPanel($panel){
    $this->_panel($panel);
  }

  function getPanel(){
    return $this->_panel;
  }

  function setCurrentTab($tab){
    $itemname = $this->getItemname();
    $_SESSION['Panel_Current_Tab'][$itemname] = $tab;
  }

  function getCurrentTab(){
    $itemname = $this->getItemname();

    if (isset($_REQUEST['tab']))
      $this->setCurrentTab($_REQUEST['tab']);

    if (isset($_SESSION['Panel_Current_Tab'][$itemname]))
      return $_SESSION['Panel_Current_Tab'][$itemname];
    else {
      $currentTab = $this->getFirstTab();
      $this->setCurrentTab($currentTab);       
      return $currentTab;
    }
  }

  function getFirstTab(){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $result = 0;
    $tabs = $this->getTabs();

    if (isset($tabs)){
      $tab = array_shift($tabs);
      $result = $tab->getId();
    }
    return $result;
  }

  function display(){
    $itemname = $this->getItemname();
    $currentTab = $this->getCurrentTab();
    $tabs = $this->getTabs();
    $panel = $this->getPanel();
    $module = $this->getModule();
    $content = $this->getContent();

    if (!isset($panel))
      $panel = CP_DEFAULT_PANEL;

    if (!isset($module))
      $module = 'controlpanel';

    foreach ($tabs as $id=>$tab){
      if ($id == $currentTab)
	$tablist[] = $tab->view(TRUE);
      else
	$tablist[] = $tab->view(FALSE);
    }

    $template['TABS'] = implode("", $tablist);
    $template['CONTENT'] = $content;

    return PHPWS_Template::process($template, $module, $panel);

  }


}

?>