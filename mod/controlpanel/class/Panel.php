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

      if (!isset($info['title']))
	return PHPWS_Error::get(CP_MISSING_TITLE, "controlpanel", "quickSetTabs");
      else
	$tab->setTitle($info['title']);

      if (!isset($info['link']))
	return PHPWS_Error::get(CP_MISSING_LINK, "controlpanel", "quickSetTabs");
      else
	$tab->setLink($info['link']);

      $tab->setOrder($count);
      $count++;
      $this->_tabs[$id] = $tab;
    }
    return TRUE;
  }

  function loadTabs(){
    $itemname = $this->getItemname();
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("itemname", $itemname);
    $DB->addOrder("tab_order");
    $DB->setIndexBy("id");
    $result = $DB->getObjects("PHPWS_Panel_Tab");

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }

    $this->setTabs($result);
  }

  function setTabs($tabs){
    if (!is_array($tabs))
      return PHPWS_Error::get("CP_BAD_TABS", "controlpanel", "setTabs");
      
    $this->_tabs = $tabs;
  }

  function getTabs(){
    return $this->_tabs;
  }

  function dropTab($id){
    unset($this->_tabs[$id]);
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
    $this->_module = $module;
  }

  function getModule(){
    return $this->_module;
  }

  function setPanel($panel){
    $this->_panel = $panel;
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

    if (isset($_REQUEST['tab']) && $itemname == $_REQUEST['module'])
      $this->setCurrentTab($_REQUEST['tab']);

    if (isset($_SESSION['Panel_Current_Tab'][$itemname])){
      return $_SESSION['Panel_Current_Tab'][$itemname];
    }
    else {
      $currentTab = $this->getFirstTab();
      $this->setCurrentTab($currentTab);       
      return $currentTab;
    }
  }

  function getFirstTab(){
    PHPWS_Core::initModClass("controlpanel", "Tab.php");
    $result = NULL;

    $tabs = $this->getTabs();

    if (isset($tabs)){
      $tab = array_shift($tabs);
      $result = $tab->getId();
    }
    return $result;
  }

  function display(){
    $itemname   = $this->getItemname();
    $currentTab = $this->getCurrentTab();
    $tabs       = $this->getTabs();
    $panel      = $this->getPanel();
    $module     = $this->getModule();
    $content    = $this->getContent();

    if (!isset($module))
      $module = 'controlpanel';

    if (!isset($panel))
      $panel = CP_DEFAULT_PANEL;

    if (!is_file(PHPWS_Template::getTplDir($module) . $panel)){
      $module = 'controlpanel';
      $panel = CP_DEFAULT_PANEL;
    }

    $tplObj = & new PHPWS_Template($module, $panel);

    if (PEAR::isError($tplObj))
      return $tplObj;

    foreach ($tabs as $id=>$tab){
      $tpl['TITLE'] = $tab->getLink();
      if ($id == $currentTab){
	$tpl['STATUS'] = "class=\"active\"";
	$tpl['ACTIVE'] = " ";
      }
      else {
	$tpl['STATUS'] = "class=\"inactive\"";
	$tpl['INACTIVE'] = " ";
      }
      $tplObj->setCurrentBlock("tabs");
      $tplObj->setData($tpl);
      $tplObj->parseCurrentBlock("tabs");
    }

    $template['CONTENT'] = $content;

    $tplObj->setData($template);
    $result = $tplObj->get();
    return $result;
  }


}

?>