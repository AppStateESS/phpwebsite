<?php

/**
 * Controls the viewing and layout of the site
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */


PHPWS_Core::initCoreClass("Template.php");

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

    $currentTheme = & $_SESSION['Layout_Settings']['current_theme'];
    if (isset($currentTheme))
      return $currentTheme;
    else
      return PHPWS_Error::get(LAYOUT_NO_THEME, "layout", "getTheme");
  }

  function getThemeVariables(){
    if (!isset($_SESSION['Layout_Settings']))
      Layout::initLayout();

    return $_SESSION['Layout_Settings']['theme_variables'];
  }

  function getThemeDir(){
    $themeDir =  Layout::getTheme();
    if (PEAR::isError($themeDir))
      return $themeDir;

    return "themes/" . $themeDir . "/";
  }

  function add($text, $module=NULL, $contentVar=NULL, $box=TRUE){
    // If content variable is not in system (and not NULL) then make
    // a new box for it.

    if (isset($module) && isset($contentVar)){
      if(!is_string($contentVar))
	return PEAR::raiseError("Content variable is not a string");

      if (!Layout::isContentVar($contentVar)){
	Layout::addBox($contentVar, $module);
	Layout_Init::initContentVar();
	Layout_Init::initBoxes();
      }
    } else {
      $box = FALSE;
      $contentVar = DEFAULT_CONTENT_VAR;
    }

    if (!is_array($text))
      $GLOBALS['Layout'][$contentVar]['content']['CONTENT'][] = $text;
    else
      foreach ($text as $key=>$value)
	$GLOBALS['Layout'][$contentVar]['content'][$key][] = $value;

    $GLOBALS['Layout'][$contentVar]['box'] = $box;
    $GLOBALS['Layout'][$contentVar]['hold']= NULL;
  }


  function set($text, $module=NULL, $contentVar=NULL, $box=TRUE){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    $GLOBALS['Layout'][$contentVar]['content'] = NULL;
    Layout::add($text, $module, $contentVar, $box);
  }

  function hold($text, $module=NULL, $contentVar=NULL, $box=TRUE, $time=NULL){
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    Layout::set($text, $module, $contentVar, $box);

    if (!isset($time) || !is_numeric($time))
      $GLOBALS['Layout'][$contentVar]['hold'] = mktime() + DEFAULT_LAYOUT_HOLD; 
    elseif($time == -1)
      $GLOBALS['Layout'][$contentVar]['hold'] = $time;
    else
      $GLOBALS['Layout'][$contentVar]['hold'] = mktime() + $time;

  }

  function clear($contentVar){
    unset($GLOBALS['Layout'][$contentVar]);
  }


  function get($content_var){
    if (isset($GLOBALS['Layout'][$content_var]))
      return $GLOBALS['Layout'][$content_var];
    else
      return NULL;
  }

  function getBoxContent(){
    $finalList = NULL;
    if (!isset($GLOBALS['Layout']))
      return PHPWS_Error::get(LAYOUT_SESSION_NOT_SET, "layout", "getBoxContent");

    foreach ($GLOBALS['Layout'] as $contentVar=>$contentList){
      if (!is_array($contentList) || !isset($contentList['content']))
	continue;

      foreach ($contentList['content'] as $tag=>$content)
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
    if (isset($GLOBALS['Layout'][$contentVar]))
      return $GLOBALS['Layout'][$contentVar]['hold'];
    else
      return 0;
  }

  function dropContentVar($contentVar){
    unset($GLOBALS['Layout'][$contentVar]);
  }

  function getBoxOrder($contentVar){
    if (isset($_SESSION['Layout_Boxes'][$contentVar]))
      return $_SESSION['Layout_Boxes'][$contentVar]['box_order'];
    else
      return NULL;
  }

  function isBoxTpl($contentVar){
    if (isset($GLOBALS['Layout'][$contentVar]))
      return $GLOBALS['Layout'][$contentVar]['box'];
    else
      return NULL;
  }

  function alternateTheme($template, $module, $file){
    $theme = Layout::getTheme();
    if (isset($GLOBALS['Style']))
      array_unshift($GLOBALS['Style'], Layout::styleLink("themes/$theme/style.css"));
    else
      $GLOBALS['Style'][] = Layout::styleLink("themes/$theme/style.css");

    $template['STYLE'] = implode("\n", $GLOBALS['Style']);
    $result = PHPWS_Template::process($template, $module, $file);
    echo $result;
    exit();
  }

  function display(){
    $themeVarList = array();
    $themeDir =  Layout::getThemeDir();
    if (!PEAR::isError($themeDir)){
      $includeFile = $themeDir . "config.php";
      if (is_file($includeFile))
	include $includeFile;
    }

    $theme = Layout::getTheme();

    $finalList = Layout::getBoxContent();

    if (!is_array($finalList)){
      $finalTheme = &Layout::loadTheme($theme);
      if (PEAR::isError($finalTheme)){
	PHPWS_Error::log($finalTheme);
	PHPWS_Core::errorPage();
      }
	

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
	$box = $_SESSION['Layout_Boxes'][$contentVar];
	$file = $box['template'];
	$directory = "themes/$theme/boxstyles/";
	if (isset($file) && is_file($directory . $file))
	  $tpl->setFile($directory . $file, TRUE);
	else
	  $tpl->setTemplate(DEFAULT_TEMPLATE);

	$tpl->setData($template);

	$unsortedLayout[$theme_var][$order] = $tpl->get();
	if (Layout::isMoveBox()){
	  Layout::addStyle("layout");
	  PHPWS_Core::initModClass("layout", "LayoutAdmin.php");
	  $unsortedLayout[$theme_var][$order] .= Layout_Admin::moveBoxesTag($box);
	}
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

  function displayErrorMessage(){
    $template[DEFAULT_THEME_VAR] = DISPLAY_ERROR_MESSAGE;
  }

  function addStyle($module, $filename=NULL){
    if (!isset($filename))
      $filename = "style.css";

    $index = $module . "_" . preg_replace("/\W/", "", $filename);

    if (FORCE_MOD_TEMPLATES){
      $cssFile = "mod/$module/templates/$filename";
      if (is_file($cssFile))
	$GLOBALS['Style'][$index] = Layout::styleLink($cssFile);
      return;
    }

    $themeFile = PHPWS_Template::getTplDir($module) . $filename;
    if (is_file($themeFile)){
      $GLOBALS['Style'][$index] = Layout::styleLink($cssFile);
      return;
    } elseif (FORCE_THEME_TEMPLATES)
	return;

    $cssFile = "templates/$module/$filename";      
    if (is_file($cssFile))
      $GLOBALS['Style'][$index] = Layout::styleLink($cssFile);

    return;
  }

  function styleLink($file){
    return "<link rel=\"stylesheet\" href=\"$file\" type=\"text/css\" />";
  }

  function isMoveBox(){
    return isset($_SESSION['Move_Boxes']);
  }


  function &loadTheme($theme, $template=NULL){
    if (!isset($template))
      Layout::displayErrorMessage();

    if (isset($GLOBALS['Style']))
      array_unshift($GLOBALS['Style'], Layout::styleLink("themes/$theme/style.css"));
    else
      $GLOBALS['Style'][] = Layout::styleLink("themes/$theme/style.css");

    $template['THEME_DIRECTORY'] = "themes/$theme/";
    $template['STYLE'] = implode("\n", $GLOBALS['Style']);

    $tpl = new PHPWS_Template;
    $themeDir = Layout::getThemeDir();

    if (PEAR::isError($themeDir)){
      	PHPWS_Error::log($themeDir);
	PHPWS_Core::errorPage();
    }

    $result = $tpl->setFile($themeDir . "theme.tpl", TRUE);

    if (PEAR::isError($result))
      return $result;

    $tpl->setData($template);
    return $tpl;
  }

  function isContentVar($content_var){
    if (!isset($_SESSION['Layout_Content_Vars']))
      return FALSE;
    return in_array($content_var, $_SESSION['Layout_Content_Vars']);
  }

  function getJavascript($script, $data=NULL){
    if (isset($data) && !is_array($data))
      return PHPWS_Error::get();

    PHPWS_CORE::initCoreClass("File.php");
    $headfile    = "javascript/$script/head.js";
    $bodyfile    = "javascript/$script/body.js";
    $defaultfile = "javascript/$script/default.php";

    if (is_file($defaultfile))
      include $defaultfile;

    if (isset($default)){
      if (isset($data))
	$data = array_merge($default, $data);
      else
	$data = $default;
    }


    if (is_file($headfile)){
      if (isset($data)){
	$tpl = new PHPWS_Template;
	$tpl->setFile($headfile, TRUE);
	$tpl->setData($data);
	$result = $tpl->get();
	if (!empty($result))
	  $GLOBALS['Layout_JS'][$script]['head'] = $result;
	else
	  $GLOBALS['Layout_JS'][$script]['head'] = PHPWS_File::readFile($headfile);	  
      } else
	$GLOBALS['Layout_JS'][$script]['head'] = PHPWS_File::readFile($headfile);
    }

    if (is_file($bodyfile)){
      if (isset($data)){
	$tpl = new PHPWS_Template;
	$tpl->setFile($bodyfile, TRUE);
	$tpl->setData($data);
	
	$result = $tpl->get();
	if (!empty($result))
	  return $result;
	else
	  return PHPWS_File::readFile($bodyfile);
      } else
	return PHPWS_File::readFile($bodyfile);
    }

  }

  function addBox($content_var, $module, $theme_var=NULL, $template=NULL, $theme=NULL){
    PHPWS_Core::initModClass("layout", "Box.php");
    PHPWS_Core::initModClass("layout", "Initialize.php");

    if (!isset($theme))
      $theme = &Layout::getTheme();
    
    if (!isset($theme_var))
      $theme_var = DEFAULT_THEME_VAR;


    $box = new Layout_Box;
    $box->setTheme($theme);
    $box->setContentVar($content_var);
    $box->setModule($module);
    $box->setThemeVar($theme_var);

    if (isset($template))
      $box->setTemplate($template);
    else
      $box->setDefaultTemplate();

    $result = $box->save();
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
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