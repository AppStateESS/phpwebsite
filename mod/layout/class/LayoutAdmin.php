<?php

define("DEFAULT_LAYOUT_TAB", "boxes");

class Layout_Admin{

  function main(){
    $content = array();
    PHPWS_Core::initModClass("controlpanel", "Panel.php");

    if (isset($_REQUEST['tab']))
      Layout_Admin::action($_REQUEST['tab'], $content);
    else {
      $panel = & new PHPWS_Panel("layout");

      $currentTab = $panel->getCurrentTab();

      if(isset($currentTab))
	Layout_Admin::action($currentTab, $content);
      else {
	$panel->setCurrentTab(DEFAULT_LAYOUT_TAB);
	Layout_Admin::action(DEFAULT_LAYOUT_TAB, $content);
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

    case "moveBox":
      $result = Layout_Admin::moveBox();
      if ($result === TRUE)
	exit(header("location:" . $_SERVER['HTTP_REFERER']));
      break;

    case "changeBoxSettings":
      Layout_Admin::saveBoxSettings();
      $message= _("Settings changed");
      Layout_Admin::boxesForm($content, $message);
      break;
      
    }
  }

  function saveBoxSettings(){
    if ($_REQUEST['move_boxes'] == 1)
      $_SESSION['Move_Boxes'] = TRUE;
    else
      PHPWS_Core::killSession("Move_Boxes");
  }

  function boxesForm(&$content, $message = NULL){
    PHPWS_Core::initCoreClass("Form.php");

    $form = new PHPWS_Form("boxes");
    $form->add("module", "hidden", "layout");
    $form->add("action[admin]", "hidden", "changeBoxSettings");
    $form->add("move_boxes", "radio", array(0, 1));
    if (isset($_SESSION['Move_Boxes']))
      $form->setMatch("move_boxes", 1);
    $template = $form->getTemplate();

    $template['MOVE_BOX_LABEL'] = _("Adjust Site Layout");
    $template['MOVE_BOXES_ON']  = _("On");
    $template['MOVE_BOXES_OFF']  = _("Off");
    $template['MESSAGE'] = $message;
    $content[] = PHPWS_Template::process($template, "layout", "BoxControl.tpl");

  }

  function moveBoxesTag($box){
    PHPWS_Core::initCoreClass("Form.php");

    $themeVars = Layout::getThemeVariables();

    $form = new PHPWS_Form;
    $form->add("module", "hidden", "layout");
    $form->add("action[admin]", "hidden", "moveBox");
    $form->add("box_source", "hidden", $box['id']);
    $form->add("box_dest", "select", $themeVars);
    $form->reindexValue("box_dest");
    $form->setMatch("box_dest", $box['theme_var']);
    $form->add("move", "submit", _("Move"));

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, "layout", "move_box_select.tpl");
  }

  function moveBox(){
    PHPWS_Core::initModClass("layout", "Box.php");
    $box = new Layout_Box($_POST['box_source']);
    $box->setThemeVar(strtoupper($_POST['box_dest']));
    $result = $box->save();
    Layout::initLayout(TRUE);
    return TRUE;
  }
}

?>