<?php

define("DEFAULT_THEME_VAR", "BODY");

class PHPWS_Layout {

  function initLayout(){
    if (!isset($_SESSION['Layout_Settings'])){
      PHPWS_Core::initModClass("layout", "Initialize.php");

      $_SESSION['Layout_Settings'] = PHPWS_Layout_Init::loadSettings();
      $_SESSION['Layout_Content_Vars'] = PHPWS_Layout_Init::loadContentVar();
      $boxes = PHPWS_Layout_Init::loadBoxes();
      if (!isset($boxes)){
	PHPWS_Layout_Init::createBoxes(PHPWS_Layout::getTheme());
	$boxes = PHPWS_Layout_Init::loadBoxes();
	if (!isset($boxes))
	  die ("initLayout was unable to load or build boxes");
      }
      $_SESSION['Layout_Boxes'] = $boxes;
    }
  }

  function getTheme(){
    if (!isset($_SESSION['Layout_Settings']))
      PHPWS_Layout::initLayout();

    return $_SESSION['Layout_Settings']['current_theme'];
  }

  function getThemeDir(){
    return "themes/" . PHPWS_Layout::getTheme() . "/";
  }

  function add($contentVar, $textArray, $box=TRUE){
      $GLOBALS['Layout'][$contentVar]['content'][] = $textArray;
      $GLOBALS['Layout'][$contentVar]['box'] = $box;
  }

  function set($contentVar, $textArray, $box=TRUE){
    $GLOBALS['Layout'][$contentVar]['content'] = array();
    PHPWS_Layout::add($contentVar, $textArray, $box);
  }


  function clear($contentVar){
    unset($GLOBALS['Layout'][$contentVar]);
  }

  function display(){
    global $Layout;
    
    $theme = PHPWS_Layout::getTheme();

    if (!isset($Layout))
      return PHPWS_Layout::loadTheme($theme);

    foreach ($Layout as $contentVar=>$contentList)
      foreach ($contentList['content'] as $content)
      foreach ($content as $key=>$value)
      $finalList[$contentVar][strtoupper($key)][] = $value;

    foreach ($finalList as $contentVar=>$contentList){
      $template = array();
      $themeVarList[] = $theme_var = $_SESSION['Layout_Boxes'][$contentVar]['theme_var'];
      $order = $_SESSION['Layout_Boxes'][$contentVar]['box_order'];

      foreach ($contentList as $tag=>$contentArray)
	  $template[strtoupper($tag)] = implode("", $contentArray);

      if ($Layout[$contentVar]['box']){
	$file = $_SESSION['Layout_Boxes'][$contentVar]['template'];
	$directory = "themes/$theme/boxstyles/";

	$tpl = new PHPWS_Template;
	$tpl->setFile($directory . $file, TRUE);
	$tpl->setData($template);

	$unsortedLayout[$theme_var][$order] = $tpl->get();
      } else
	$unsortedLayout[$theme_var][$order] = implode("", $template);

    }

    foreach ($themeVarList as $theme_var){
      ksort($unsortedLayout[$theme_var]);
      $finalLayout[strtoupper($theme_var)] = implode("", $unsortedLayout[$theme_var]);
    }

    return PHPWS_Layout::loadTheme($theme, $finalLayout);
  }


  function loadTheme($theme, $template=NULL){
    if (!isset($template))
      $template[DEFAULT_THEME_VAR] = "No content to display!";

    $template['THEME_DIRECTORY'] = "themes/$theme/";
    $template['STYLE'] = "<link rel=\"stylesheet\" href=\"themes/Default/style.css\" type=\"text/css\" />";

    $tpl = new PHPWS_Template;
    $tpl->setFile(PHPWS_Layout::getThemeDir() . "theme.tpl", TRUE);
    $tpl->setData($template);
    return $tpl->get();
  }


  function isContentVar($content_var){
    return isset($_SESSION['Layout_Content_Vars'][$content_var]);
  }

  function createBox($theme, $content_var, $theme_var, $template){
    PHPWS_Core::initModClass("layout", "Box.php");

    $box = new PHPWS_Layout_Box;
    $box->setTheme($theme);
    $box->setContentVar($content_var);
    $box->setThemeVar($theme_var);
    $box->setTemplate($template);
    $box->save();
  }

}

?>