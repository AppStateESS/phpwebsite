<?php

define("DEFAULT_LAYOUT_TAB", "boxes");

class Layout_Admin{

  function admin(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $content = NULL;
    $panel = & new PHPWS_Panel("layout");

    if (isset($_REQUEST['sub']))
      $command = $_REQUEST['sub'];
    else
      $command = $panel->getCurrentTab();

    switch ($command){
    case "boxes":
      $content = Layout_Admin::boxesForm();
      break;

    case "meta":
      $content = Layout_Admin::metaForm();
      break;

    case "moveBox":
      $result = Layout_Admin::moveBox();
      if ($result === TRUE)
	exit(header("location:" . $_SERVER['HTTP_REFERER']));
      break;

    case "changeBoxSettings":
      Layout_Admin::saveBoxSettings();
      $message= _("Settings changed");
      $content = Layout_Admin::boxesForm($message);
      break;
    }

    Layout_Admin::adminPanel($content);
  }


  function adminPanel($content){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    Layout::addStyle("layout");

    $tabs["boxes"] = array("title"=>"Boxes", "link"=>"index.php?module=layout&amp;action=admin");
    $tabs["meta"]  = array("title"=>"Meta Tags", "link"=>"index.php?module=layout&amp;action=admin");

    $panel = & new PHPWS_Panel("layout");
    $panel->quickSetTabs($tabs);
    $panel->setContent($content);
    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }

  function saveBoxSettings(){
    if ($_REQUEST['move_boxes'] == 1)
      $_SESSION['Move_Boxes'] = TRUE;
    else
      PHPWS_Core::killSession("Move_Boxes");
  }

  function metaForm(){
    $form = & new PHPWS_Form("metatags");
    $form->addHidden("module", "layout");
    
  }

  function boxesForm($message = NULL){
    $form = & new PHPWS_Form("boxes");
    $form->add("module", "hidden", "layout");
    $form->add("action[admin]", "hidden", "changeBoxSettings");
    $form->add("move_boxes", "radio", array(0, 1));
    if (isset($_SESSION['Move_Boxes']))
      $form->setMatch("move_boxes", 1);
    else
      $form->setMatch("move_boxes", 0);

    $form->addSubmit("default_submit", "Change Settings");

    $template = $form->getTemplate();

    $template['MOVE_BOX_LABEL'] = _("Adjust Site Layout");
    $template['MOVE_BOXES_ON']  = _("On");
    $template['MOVE_BOXES_OFF']  = _("Off");
    $template['MESSAGE'] = $message;
    return PHPWS_Template::process($template, "layout", "BoxControl.tpl");
  }


  function moveBoxesTag($box){
    PHPWS_Core::initCoreClass("Form.php");

    $themeVars = Layout::getThemeVariables();

    $menu["up"] = _("Move Up");
    $menu["down"] = _("Move Down");
    foreach ($themeVars as $var){
      if ($box['theme_var'] == $var)
	continue;
      $menu[$var] = _("Move to") . " " . $var;
    }

    $form = new PHPWS_Form;
    $form->add("module", "hidden", "layout");
    $form->add("action[admin]", "hidden", "moveBox");
    $form->add("box_source", "hidden", $box['id']);
    $form->add("box_dest", "select", $menu);
    $form->setMatch("box_dest", $box['theme_var']);
    $form->add("move", "submit", _("Move"));

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, "layout", "move_box_select.tpl");
  }

  function moveBox(){
    PHPWS_Core::initModClass("layout", "Box.php");
    $box = new Layout_Box($_POST['box_source']);

    $currentThemeVar = $box->getThemeVar();

    if ($_POST['box_dest'] == "up")
      $box->moveUp();
    elseif ($_POST['box_dest'] == "down")
      $box->moveDown();
    else {
      $box->setThemeVar($_POST['box_dest']);
      $box->setBoxOrder(NULL);
      $result = $box->save();
    }

    Layout_Box::reorderBoxes($box->getTheme(), $currentThemeVar);

    Layout::initLayout(TRUE);
    return TRUE;
  }
}

?>