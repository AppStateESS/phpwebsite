<?php

class Layout {

  function initLayout($refresh=FALSE){
    if ($refresh == TRUE || !isset($_SESSION['Layout_Settings'])){
      PHPWS_Core::initModClass("layout", "Initialize.php");
      Layout_Init::initSettings();
      Layout_Init::initContentVar();
      Layout_Init::initBoxes();
    }

    $boxes = Layout::getBoxes();

    if (!isset($boxes)){
      PHPWS_Core::initModClass("layout", "Initialize.php");
      $boxes = Layout_Init::loadBoxes();
    }
    $_SESSION['Layout_Boxes'] = $boxes;
  }


  function &getTheme(){
    if (!isset($_SESSION['Layout_Settings']))
      Layout::initLayout();

    return $_SESSION['Layout_Settings']['current_theme'];
  }

  function getThemeDir(){
    return "themes/" . Layout::getTheme() . "/";
  }

  function add($text, $contentVar=NULL, $box=TRUE){
    // If content variable is not in system (and not NULL) then make
    // a new box for it.

    if (isset($contentVar)){
      if(!is_string($contentVar))
	return PEAR::raiseError("Content variable is not a string");

      if (!Layout::isContentVar($contentVar)){
	Layout::addBox($contentVar);
	Layout_Init::initContentVar();
	Layout_Init::initBoxes();
      }
    } else {
      $box = FALSE;
      $contentVar = DEFAULT_CONTENT_VAR;
    }

    if (!is_array($text))
      $_SESSION['Layout'][$contentVar]['content']['CONTENT'][] = $text;
    else
      foreach ($text as $key=>$value)
	$_SESSION['Layout'][$contentVar]['content'][$key][] = $value;

    $_SESSION['Layout'][$contentVar]['box'] = $box;
    $_SESSION['Layout'][$contentVar]['hold']= NULL;
  }


  function set($text, $contentVar=NULL, $box=TRUE){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    $_SESSION['Layout'][$contentVar]['content'] = NULL;
    Layout::add($text, $contentVar, $box);
  }

  function hold($text, $contentVar=NULL, $box=TRUE, $time=NULL){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    Layout::set($text, $contentVar, $box);

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

  function getBoxContent(){
    $finalList = NULL;
    if (!isset($_SESSION['Layout']))
      return PHPWS_Error::get(LAYOUT_SESSION_NOT_SET, "layout", "getBoxContent");

    foreach ($_SESSION['Layout'] as $contentVar=>$contentList)
      foreach ($contentList['content'] as $tag=>$content){
      $finalList[$contentVar][strtoupper($tag)] = implode("", $content);
    }
    return $finalList;
  }

  function getBoxThemeVar($contentVar){
    if (isset($_SESSION['Layout_Boxes'][$contentVar]))
      return $_SESSION['Layout_Boxes'][$contentVar]['theme_var'];
    else
      return NULL;
  }

  function getBoxHold($contentVar){
    if (isset($_SESSION['Layout'][$contentVar]))
      return $_SESSION['Layout'][$contentVar]['hold'];
    else
      return 0;
  }

  function dropContentVar($contentVar){
    unset($_SESSION['Layout'][$contentVar]);
  }

  function getBoxOrder($contentVar){
    if (isset($_SESSION['Layout_Boxes'][$contentVar]))
      return $_SESSION['Layout_Boxes'][$contentVar]['box_order'];
    else
      return NULL;
  }

  function isBoxTpl($contentVar){
    if (isset($_SESSION['Layout'][$contentVar]))
      return $_SESSION['Layout'][$contentVar]['box'];
    else
      return NULL;
  }

  function display(){
    $themeVarList = array();
    $includeFile = Layout::getThemeDir() . "config.php";

    if (is_file($includeFile))
      include $includeFile;

    $theme = Layout::getTheme();

    $finalList = Layout::getBoxContent();

    if (!is_array($finalList)){
      $finalTheme = &Layout::loadTheme($theme);

      if (!isset($finalList))
	PHPWS_Error::log(LAYOUT_NO_CONTENT, "layout", "display");
      elseif (PEAR::isError($finalList))
	PHPWS_Error::log($finalList);
      
      echo $finalTheme->get();
      return;
    }


    foreach ($finalList as $contentVar=>$template){

      // Need to check for theme variable
      if(!($theme_var = Layout::getBoxThemeVar($contentVar)))
	$theme_var = DEFAULT_THEME_VAR;

      if (!in_array($theme_var, $themeVarList))
	$themeVarList[] = $theme_var;

      $order = Layout::getBoxOrder($contentVar);

      if (!isset($order))
	$order = MAX_ORDER_VALUE;

      if (Layout::isBoxTpl($contentVar)){
	$tpl = new PHPWS_Template;

	$file = $_SESSION['Layout_Boxes'][$contentVar]['template'];
	$directory = "themes/$theme/boxstyles/";
	if (isset($file) && is_file($directory . $file))
	  $tpl->setFile($directory . $file, TRUE);
	else
	  $tpl->setTemplate(DEFAULT_TEMPLATE);

	$tpl->setData($template);

	$unsortedLayout[$theme_var][$order] = $tpl->get();
      } else {
	$unsortedLayout[$theme_var][$order] = implode("", $template);
      }

      $hold = Layout::getBoxHold($contentVar);

      if($hold > mktime() || (bool)$hold == FALSE)
	Layout::dropContentVar($contentVar);
    }


    if (isset($themeVarList)){
      foreach ($themeVarList as $theme_var){
	ksort($unsortedLayout[$theme_var]);
	$finalLayout[strtoupper($theme_var)] = implode("", $unsortedLayout[$theme_var]);
      }
    } else
      $finalLayout[] = implode("<br />", $unsortedLayout[$theme_var]);

    if (isset($GLOBALS['Layout_JS'])){
      foreach ($GLOBALS['Layout_JS'] as $script=>$javascript)
	$jsHead[] = $javascript['head'];

      if (isset($jsHead))
	$finalLayout['JAVASCRIPT'] = implode("\n", $jsHead);
    }

      $finalTheme = &Layout::loadTheme($theme, $finalLayout);

    if (PEAR::isError($finalTheme))
      echo implode("", $finalLayout);
    else
      echo $finalTheme->get();
  }


  function &loadTheme($theme, $template=NULL){
    if (!isset($template))
      $template[DEFAULT_THEME_VAR] = DISPLAY_ERROR_MESSAGE;

    $template['THEME_DIRECTORY'] = "themes/$theme/";
    $template['STYLE'] = "<link rel=\"stylesheet\" href=\"themes/$theme/style.css\" type=\"text/css\" />";

    $tpl = new PHPWS_Template;
    $result = $tpl->setFile(Layout::getThemeDir() . "theme.tpl", TRUE);

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
      $GLOBALS['Layout_JS'][$script]['head'] = implode("", file($headfile));

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
      $theme = &Layout::getTheme();
    
    if (!isset($theme_var))
      $theme_var = DEFAULT_THEME_VAR;


    $box = new Layout_Box;
    $box->setTheme($theme);
    $box->setContentVar($content_var);
    $box->setThemeVar($theme_var);

    if (isset($template))
      $box->setTemplate($template);
    else
      $box->setDefaultTemplate();

    $result = $box->save();
    if (PEAR::isError($result)){
      echo $result->getMessage();
      echo phpws_debug::testobject($result);
      exit();
    }

  }


  function getBoxes(){
    return $_SESSION['Layout_Boxes'];
  }


  function getContentVars(){
    if (isset($_SESSION['Layout_Content_Vars']))
      $content_vars = $_SESSION['Layout_Content_Vars'];
    else
      $content_vars = Layout_Init::loadContentVar();

    return $content_vars;
  }

}

?>