<?php

define("DEFAULT_LAYOUT_TAB", "boxes");

class Layout_Admin{

  function main(){
    $content = array();
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    Layout::addStyle("layout");

    if (isset($_REQUEST['tab']))
      Layout_Admin::action($_REQUEST['tab'], $content);
    else {
      $panel = & new PHPWS_Panel("layout");

      $currentTab = $panel->getCurrentTab();

      if(isset($currentTab))
	$content = Layout_Admin::action($currentTab, $content);
      else {
	$panel->setCurrentTab(DEFAULT_LAYOUT_TAB);
	$content = Layout_Admin::action(DEFAULT_LAYOUT_TAB, $content);
      }
    }

    Layout_Admin::adminPanel(implode("", $content));
  }


  function adminPanel($content){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    $tabs["boxes"] = array("title"=>"Boxes", "link"=>"index.php?module=layout&amp;action[admin]=main");
    $tabs["meta"]  = array("title"=>"Meta Tags", "link"=>"index.php?module=layout&amp;action[admin]=main");

    $panel = & new PHPWS_Panel("layout");
    $panel->quickSetTabs($tabs);
    $panel->setContent($content);
    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }

  function action($command, &$content){
    switch ($command){
    case "boxes":
      Layout_Admin::boxesForm($content);
      break;

    case "meta":

      break;
      
    }
  }


  function boxesForm(&$content){
    PHPWS_Core::initCoreClass("Form.php");


    $form = new PHPWS_Form("boxes");
    $form->add("move_boxes", "radio", array(0, 1));
    $template = $form->getTemplate();

    $template['MOVE_BOX_LABEL'] = _("Adjust Site Layout");
    $template['MOVE_BOXES_ON']  = _("On");
    $template['MOVE_BOXES_OFF']  = _("Off");

    $content[] = PHPWS_Template::process($template, "layout", "BoxControl.tpl");

  }

}

?>