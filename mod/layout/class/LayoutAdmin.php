<?php

define("DEFAULT_LAYOUT_TAB", "boxes");

class Layout_Admin{

  function admin(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $content = NULL;
    $panel = Layout_Admin::adminPanel();

    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
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
    $panel->setContent($content);
    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }


  function &adminPanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    Layout::addStyle("layout");

    $tabs["boxes"] = array("title"=>"Boxes", "link"=>"index.php?module=layout&amp;action=admin");
    $tabs["meta"]  = array("title"=>"Meta Tags", "link"=>"index.php?module=layout&amp;action=admin");

    $panel = & new PHPWS_Panel("layout");
    $panel->quickSetTabs($tabs);
    return $panel;
  }

  function saveBoxSettings(){
    if ($_REQUEST['move_boxes'] == 1)
      $_SESSION['Move_Boxes'] = TRUE;
    else
      PHPWS_Core::killSession("Move_Boxes");
  }

  function metaForm(){
    extract($_SESSION['Layout_Settings']);

    $form = & new PHPWS_Form("metatags");
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postMeta");
    $form->addText("page_title", $page_title);
    $form->setLabel("page_title", _("Page Title"));
    $form->addTextArea("meta_keywords", $meta_keywords);
    $form->setLabel("meta_keywords", _("Keywords"));
    $form->addTextArea("meta_description", $meta_description);
    $form->setLabel("meta_description", _("Description"));
    $form->addSubmit("submit", _("Update"));

    $template = $form->getTemplate();

    return PHPWS_Template::process($template, "layout", "metatags.tpl");
  }

  function boxesForm($message = NULL){
    $form = & new PHPWS_Form("boxes");
    $form->add("module", "hidden", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "changeBoxSettings");
    $form->addRadio("move_boxes",  array(0, 1));
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
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "moveBox");
    $form->addHidden("box_source", $box['id']);
    $form->addSelect("box_dest", $menu);
    $form->setMatch("box_dest", $box['theme_var']);
    $form->addSubmit("move", _("Move"));

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