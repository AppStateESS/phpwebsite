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
      $template['TITLE'] = _("Adjust Boxes");
      $content = Layout_Admin::boxesForm();
      break;

    case "changeBoxSettings":
      Layout_Admin::saveBoxSettings();
      $template['TITLE'] = _("Adjust Boxes");
      $template['MESSAGE'] = _("Settings changed");
      $content = Layout_Admin::boxesForm();
      break;

    case "confirmThemeChange":
      Layout_Admin::changeTheme($_POST['theme']);
      $template['TITLE'] = _("Themes");
      $template['MESSAGE'] = _("Theme settings updated.");
      $content = Layout_Admin::adminThemes();
      break;

    case "meta":
      $template['TITLE'] = _("Edit Meta Tags");
      $content = Layout_Admin::metaForm();
      break;

    case "moveBox":
      $result = Layout_Admin::moveBox();
      if ($result === TRUE)
	exit(header("location:" . $_SERVER['HTTP_REFERER']));
      break;

    case "postMeta":
      PHPWS_Core::initModClass("layout", "Initialize.php");
      Layout_Admin::postMeta();
      Layout::reset();
      $template['TITLE'] = _("Edit Meta Tags");
      $template['MESSAGE'] = _("Meta Tags updated.");
      $content = Layout_Admin::metaForm();
      break;

    case "postTheme":
      if ($_POST['default_theme'] == $_SESSION['Layout_Settings']['default_theme']){
	Layout_Admin::postTheme();
	$template['TITLE'] = _("Themes");
	$template['MESSAGE'] = _("Theme settings updated.");
	$content = Layout_Admin::adminThemes();
      }
      else {
	$template['TITLE'] = _("Confirm Theme Change");
	$content = Layout_Admin::confirmThemeChange();
      }
      break;

    case "theme":
      $template['TITLE'] = _("Themes");
      $content = Layout_Admin::adminThemes();
      break;
    }

    $template['CONTENT'] = $content;
    
    $final = PHPWS_Template::process($template, "layout", "main.tpl");
    $panel->setContent($final);

    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }


  function &adminPanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    Layout::addStyle("layout");

    $tabs["boxes"] = array("title"=>_("Boxes"), "link"=>"index.php?module=layout&amp;action=admin");
    $tabs["meta"]  = array("title"=>_("Meta Tags"), "link"=>"index.php?module=layout&amp;action=admin");
    $tabs["theme"]  = array("title"=>_("Themes"), "link"=>"index.php?module=layout&amp;action=admin");

    $panel = & new PHPWS_Panel("layout");
    $panel->quickSetTabs($tabs);
    return $panel;
  }

  function adminThemes(){
    $form = & new PHPWS_Form("themes");
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postTheme");

    $form->addSubmit("update", _("Update Theme Settings"));
    $themeList = Layout_Admin::getThemeList();
    if (PEAR::isError($themeList)){
      PHPWS_Error::log($themeList);
      return _("There was a problem reading the theme directories.");
    }

    $form->addSelect("default_theme", $themeList);
    $form->reindexValue("default_theme");
    $form->setMatch("default_theme", Layout::getDefaultTheme());
    $form->setLabel("default_theme", _("Default Theme"));

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, "layout", "themes.tpl");
  }

  function boxesForm(){
    $form = & new PHPWS_Form("boxes");
    $form->add("module", "hidden", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "changeBoxSettings");
    $form->addRadio("move_boxes",  array(0, 1));
    if (Layout::isMoveBox())
      $form->setMatch("move_boxes", 1);
    else
      $form->setMatch("move_boxes", 0);

    $form->addSubmit("default_submit", "Change Settings");

    $template = $form->getTemplate();

    $template['MOVE_BOX_LABEL'] = _("Adjust Site Layout");
    $template['MOVE_BOXES_ON']  = _("On");
    $template['MOVE_BOXES_OFF']  = _("Off");
    return PHPWS_Template::process($template, "layout", "BoxControl.tpl");
  }

  function changeTheme($theme){
    $db = & new PHPWS_DB("layout_config");
    $db->addValue("default_theme", $theme);
    $db->update();
  }

  function confirmThemeChange(){
    $form = & new PHPWS_Form("confirmThemeChange");
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "confirmThemeChange");
    $form->addHidden("theme", $_POST['default_theme']);
    $form->addSubmit("confirm", _("Click here to complete the theme change."));
    return $form->getMerge();
  }

  function getThemeList(){
    PHPWS_Core::initCoreClass("File.php");
    return PHPWS_File::readDirectory("themes/", 1);
  }

  function metaForm(){
    extract($_SESSION['Layout_Settings']->getMetaTags());

    $index = substr($meta_robots, 0, 1);
    $follow = substr($meta_robots, 1, 1);

    $form = & new PHPWS_Form("metatags");
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postMeta");
    $form->addText("page_title", $page_title);
    $form->setSize("page_title", 40);
    $form->setLabel("page_title", _("Page Title"));
    $form->addTextArea("meta_keywords", $meta_keywords);
    $form->setLabel("meta_keywords", _("Keywords"));
    $form->addTextArea("meta_description", $meta_description);
    $form->setLabel("meta_description", _("Description"));
    $form->addCheckBox("index", 1);
    $form->setMatch("index", $index);
    $form->setLabel("index", _("Allow Indexing"));
    $form->addCheckBox("follow", 1);
    $form->setMatch("follow", $follow);
    $form->setLabel("follow", _("Allow Link Following"));

    $form->addSubmit("submit", _("Update"));

    $template = $form->getTemplate();
    $template['ROBOT_LABEL'] = _("Default Robot Settings");

    return PHPWS_Template::process($template, "layout", "metatags.tpl");
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

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      Layout::add("An unexpected error occurred when trying to save the new box position.");
      return;
    }

    Layout_Box::reorderBoxes($box->getTheme(), $currentThemeVar);
    Layout::resetBoxes();
    return TRUE;
  }

  function moveBoxesTag($box){
    PHPWS_Core::initCoreClass("Form.php");

    $themeVars = $_SESSION['Layout_Settings']->getThemeVariables();

    $menu["up"] = _("Move Up");
    $menu["down"] = _("Move Down");
    foreach ($themeVars as $var){
      if ($box->theme_var == $var)
	continue;
      $menu[$var] = _("Move to") . " " . $var;
    }

    $form = new PHPWS_Form;
    $form->addHidden("module", "layout");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "moveBox");
    $form->addHidden("box_source", $box->id);
    $form->addSelect("box_dest", $menu);
    $form->setMatch("box_dest", $box->theme_var);
    $form->addSubmit("move", _("Move"));

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, "layout", "move_box_select.tpl");
  }

  function postMeta(){
    extract($_POST);
    
    $values['page_title'] = strip_tags($page_title);
    $values['meta_keywords'] = strip_tags($meta_keywords);
    $values['meta_description'] = strip_tags($meta_description);

    if (isset($_POST['index']))
      $index = 1;
    else
      $index = 0;

    if (isset($_POST['follow']))
      $follow = 1;
    else
      $follow = 0;

    $values['meta_robots'] = $index . $follow;
    
    $db = & new PHPWS_DB("layout_config");
    $db->addValue($values);
    $db->update();
  }

  function postTheme(){
    echo "post";
  }

  function saveBoxSettings(){
    if ($_REQUEST['move_boxes'] == 1)
      Layout::moveBoxes(TRUE);
    else
      Layout::moveBoxes(FALSE);
  }


}

?>