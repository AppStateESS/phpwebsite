<?php

define("DEFAULT_THEME_VAR", "BODY");
define("DEFAULT_LAYOUT_HOLD", 20);

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

  function add($contentVar, $text, $box=TRUE){
    //need to check for existance of contentvar

    if (!is_array($text))
      $content['CONTENT'] = $text;
    else
      $content = $text;

    $box = (bool)$box;
    $_SESSION['Layout'][$contentVar]['content'][] = $content;
    $_SESSION['Layout'][$contentVar]['box'] = $box;
    $_SESSION['Layout'][$contentVar]['hold']= NULL;
  }

  function set($contentVar, $textArray, $box=TRUE){
    $box = (bool)$box;
    $_SESSION['Layout'][$contentVar]['content'] = array();
    PHPWS_Layout::add($contentVar, $textArray, $box);
  }

  function hold($contentVar, $textArray, $box=TRUE, $time=NULL){
    $box = (bool)$box;

    PHPWS_Layout::set($contentVar, $textArray, $box);

    if (!isset($time) || !is_numeric($time))
      $_SESSION['Layout'][$contentVar]['hold'] = mktime() + DEFAULT_LAYOUT_HOLD; 
    elseif($time == -1)
      $_SESSION['Layout'][$contentVar]['hold'] = $time;
    else
      $_SESSION['Layout'][$contentVar]['hold'] = mktime() + $time;

  }

  function clear($contentVar){
    unset($_SESSION['Layout'][$contentVar]);
  }


  function get($content_var){
    if (isset($_SESSION['Layout'][$content_var]))
      return $_SESSION['Layout'][$content_var];
    else
      return NULL;
  }


  function display(){
    $Layout = &$_SESSION['Layout'];

    $theme = PHPWS_Layout::getTheme();

    if (!isset($Layout))
      return PHPWS_Layout::loadTheme($theme);

    foreach ($Layout as $contentVar=>$contentList)
      foreach ($contentList['content'] as $content)
      foreach ($content as $key=>$value)
      $finalList[$contentVar][strtoupper($key)][] = $value;

    foreach ($finalList as $contentVar=>$contentList){
      $template = array();
      // Need to check for existance of box
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

      if(!isset($Layout[$contentVar]['hold']) || ($Layout[$contentVar]['hold'] > -1 && $Layout[$contentVar]['hold'] < mktime()))
	unset($Layout[$contentVar]);
    }

    foreach ($themeVarList as $theme_var){
      ksort($unsortedLayout[$theme_var]);
      $finalLayout[strtoupper($theme_var)] = implode("", $unsortedLayout[$theme_var]);
    }

    if (isset($GLOBALS['Layout_JS'])){
      foreach ($GLOBALS['Layout_JS'] as $script=>$javascript)
	$jsHead[] = $javascript['head'];

      if (isset($jsHead))
	$finalLayout['JAVASCRIPT'] = implode("\n", $jsHead);
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

  function addJS($script, $data){
    PHPWS_CORE::initCoreClass("File.php");
    $headfile = "java/$script/head.js";
    $bodyfile = "java/$script/body.js";

    if (is_file($headfile))
      $GLOBALS['Layout_JS'][$script]['head'] = PHPWS_File::readFile($headfile);

    if (is_file($bodyfile)){
      $tpl = new PHPWS_Template();
      $tpl->setFile($bodyfile, TRUE);
      $tpl->setData($data);

      return $tpl->get();
    }

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