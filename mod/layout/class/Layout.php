<?php

class PHPWS_Layout {

  function initLayout($refresh=FALSE){
    if ($refresh == TRUE || !isset($_SESSION['Layout_Settings'])){
      PHPWS_Core::initModClass("layout", "Initialize.php");
      PHPWS_Layout_Init::initSettings();
      PHPWS_Layout_Init::initContentVar();
      PHPWS_Layout_Init::initBoxes();
    }

    $boxes = PHPWS_Layout::getBoxes();

    if (!isset($boxes)){
      PHPWS_Core::initModClass("layout", "Initialize.php");
      $boxes = PHPWS_Layout_Init::loadBoxes();
    }
    $_SESSION['Layout_Boxes'] = $boxes;
  }


  function &getTheme(){
    if (!isset($_SESSION['Layout_Settings']))
      PHPWS_Layout::initLayout();

    return $_SESSION['Layout_Settings']['current_theme'];
  }

  function getThemeDir(){
    return "themes/" . PHPWS_Layout::getTheme() . "/";
  }

  function add($text, $contentVar=NULL, $box=TRUE){
    // If content variable is not in system (and not NULL) then make
    // a new box for it.

    if (isset($contentVar) && !PHPWS_Layout::isContentVar($contentVar)){
      PHPWS_Layout::addBox($contentVar);
      PHPWS_Layout_Init::initContentVar();
    }

    if (!isset($contentVar)){
      $box = FALSE;
      $contentVar = DEFAULT_CONTENT_VAR;
    }

    if (!is_array($text))
      $content['CONTENT'] = $text;
    else
      $content = $text;

    $_SESSION['Layout'][$contentVar]['content'][] = $content;
    $_SESSION['Layout'][$contentVar]['box'] = $box;
    $_SESSION['Layout'][$contentVar]['hold']= NULL;
  }


  function set($text, $contentVar=NULL, $box=TRUE){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    $_SESSION['Layout'][$contentVar]['content'] = array();
    PHPWS_Layout::add($text, $contentVar, $box);
  }

  function hold($text, $contentVar=NULL, $box=TRUE, $time=NULL){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    PHPWS_Layout::set($text, $contentVar, $box);

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

  function getBoxContent($contentVar){
    if (isset($_SESSION['Layout_Boxes'][$contentVar]))
      return $_SESSION['Layout_Boxes'][$contentVar]['theme_var'];
    else
      return NULL;
  }

  function getBoxOrder($contentVar){
    if (isset($_SESSION['Layout_Boxes'][$contentVar]))
      return $_SESSION['Layout_Boxes'][$contentVar]['box_order'];
    else
      return NULL;
  }

  function display(){
    $Layout = &$_SESSION['Layout'];
    $includeFile = PHPWS_Layout::getThemeDir() . "config.php";

    if (is_file($includeFile)){
      include $includeFile;
    }

    $theme = PHPWS_Layout::getTheme();

    if (!isset($Layout))
      return PHPWS_Layout::loadTheme($theme);

    foreach ($Layout as $contentVar=>$contentList)
      foreach ($contentList['content'] as $content)
      foreach ($content as $key=>$value)
      $finalList[$contentVar][strtoupper($key)][] = $value;

    if (!isset($finalList))
      return NULL;

    foreach ($finalList as $contentVar=>$contentList){
      $template = array();
      // Need to check for existance of box
      $theme_var = PHPWS_Layout::getBoxContent($contentVar);
      if (isset($theme_var))
	$themeVarList[] = $theme_var;
      else {
	$themeVarList = NULL;
	$theme_var = "_BLANK";
      }
      
      $order = PHPWS_Layout::getBoxOrder($contentVar);

      if (!isset($order))
	$order = 1;

      foreach ($contentList as $tag=>$contentArray)
	  $template[strtoupper($tag)] = implode("", $contentArray);

      if ($Layout[$contentVar]['box']){
	$tpl = new PHPWS_Template;

	$file = $_SESSION['Layout_Boxes'][$contentVar]['template'];
	$directory = "themes/$theme/boxstyles/";

	if (isset($file))
	  $tpl->setFile($directory . $file, TRUE);
	else
	  $tpl->setTemplate(DEFAULT_TEMPLATE);

	$tpl->setData($template);

	$unsortedLayout[$theme_var][$order] = $tpl->get();
      } else
	$unsortedLayout[$theme_var][$order] = implode("", $template);

      if(!isset($Layout[$contentVar]['hold']) || ($Layout[$contentVar]['hold'] > -1 && $Layout[$contentVar]['hold'] < mktime()))
	unset($Layout[$contentVar]);
    }

    if (isset($themeVarList)){
      foreach ($themeVarList as $theme_var){
	ksort($unsortedLayout[$theme_var]);
	$finalLayout[strtoupper($theme_var)] = implode("", $unsortedLayout[$theme_var]);
      }
    } else
      $finalLayout[DEFAULT_THEME_VAR] = implode("<br />", $unsortedLayout[$theme_var]);

    if (isset($GLOBALS['Layout_JS'])){
      foreach ($GLOBALS['Layout_JS'] as $script=>$javascript)
	$jsHead[] = $javascript['head'];

      if (isset($jsHead))
	$finalLayout['JAVASCRIPT'] = implode("\n", $jsHead);
    }
    
    $finalTheme = &PHPWS_Layout::loadTheme($theme, $finalLayout);

    if (PEAR::isError($finalTheme))
      echo implode("", $finalLayout);
    else
      echo $finalTheme->get();
  }


  function &loadTheme($theme, $template=NULL){
    if (!isset($template))
      $template[DEFAULT_THEME_VAR] = "No content to display!";

    $template['THEME_DIRECTORY'] = "themes/$theme/";
    $template['STYLE'] = "<link rel=\"stylesheet\" href=\"themes/Default/style.css\" type=\"text/css\" />";

    $tpl = new PHPWS_Template;
    $result = $tpl->setFile(PHPWS_Layout::getThemeDir() . "theme.tpl", TRUE);

    if (PEAR::isError($result))
      return $result;

    $tpl->setData($template);
    return $tpl;
  }


  function isContentVar($content_var){
    return in_array($content_var, $_SESSION['Layout_Content_Vars']);
  }

  function addJS($script, $data){
    PHPWS_CORE::initCoreClass("File.php");
    $headfile = "java/$script/head.js";
    $bodyfile = "java/$script/body.js";

    if (is_file($headfile))
      $GLOBALS['Layout_JS'][$script]['head'] = PHPWS_File::readFile($headfile);

    if (is_file($bodyfile)){
      $tpl = new PHPWS_Template;
      $tpl->setFile($bodyfile, TRUE);
      $tpl->setData($data);

      return $tpl->get();
    }

  }

  function addBox($content_var, $theme_var=NULL, $template=NULL, $theme=NULL){
    PHPWS_Core::initModClass("layout", "Box.php");
    PHPWS_Core::initModClass("layout", "Initialize.php");

    if (!isset($theme))
      $theme = &PHPWS_Layout::getTheme();
    
    if (!isset($theme_var))
      $theme_var = DEFAULT_THEME_VAR;

    $box = new PHPWS_Layout_Box;
    $box->setTheme($theme);
    $box->setContentVar($content_var);
    $box->setThemeVar($theme_var);

    if (isset($template))
      $box->setTemplate($template);
    else
      $box->setDefaultTemplate();


    $result = $box->save();
    if (PEAR::isError($result))
      echo $result->getMessage();

  }


  function getBoxes(){
    return $_SESSION['Layout_Boxes'];
  }


  function getContentVars(){
    if (isset($_SESSION['Layout_Content_Vars']))
      $content_vars = $_SESSION['Layout_Content_Vars'];
    else
      $content_vars = PHPWS_Layout_Init::loadContentVar();

    return $content_vars;
  }

}

?>