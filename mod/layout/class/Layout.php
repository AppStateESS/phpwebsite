<?php

/**
 * Controls the viewing and layout of the site
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

PHPWS_CORE::initModClass("layout", "Layout_Settings.php");
PHPWS_Core::initCoreClass("Template.php");

class Layout {

  function add($text, $module=NULL, $contentVar=NULL, $box=TRUE){
    Layout::checkSettings();
    // If content variable is not in system (and not NULL) then make
    // a new box for it.
    
    if (isset($module) && isset($contentVar)){
      if (!$_SESSION['Layout_Settings']->isContentVar($contentVar))
	Layout::addBox($contentVar, $module);
    } else {
      $box = FALSE;
      $module = "layout";
      $contentVar = DEFAULT_CONTENT_VAR;
    }

    if (!is_array($text))
      $GLOBALS['Layout'][$module][$contentVar]['content']['CONTENT'][] = $text;
    else
      foreach ($text as $key=>$value)
	$GLOBALS['Layout'][$module][$contentVar]['content'][$key][] = $value;

    $GLOBALS['Layout'][$module][$contentVar]['box'] = $box;

  }

  function addBox($content_var, $module, $theme_var=NULL, $template=NULL, $theme=NULL){
    PHPWS_Core::initModClass("layout", "Box.php");

    if (!isset($theme))
      $theme = $_SESSION['Layout_Settings']->current_theme;
    
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
      PHPWS_Core::errorPage();
    }

  }

  function addJSFile($directory){
    $jsfile = PHPWS_SOURCE_DIR . $directory;

    if (!is_file($jsfile))
      return PHPWS_Error::get(LAYOUT_JS_FILE_NOT_FOUND, "layout", "addJSFile", $jsfile);
  }

  function addJSHeader($script, $index=NULL){
    static $index_count = 0;

    if (empty($index))
      $index = $index_count++;
    
    $GLOBALS['Layout_JS'][$index]['head'] = $script;
  }

  function addOnLoad($onload){
    $GLOBALS['Layout_Onload'][] = $onload;
  }

  function addStyle($module, $filename=NULL){
    if (!isset($filename))
      $filename = "style.css";

    $index = $module . "_" . preg_replace("/\W/", "", $filename);

    if (FORCE_MOD_TEMPLATES){
      $cssFile = "mod/$module/templates/$filename";
      if (is_file(PHPWS_SOURCE_DIR . $cssFile))
	Layout::addToStyleList(array('file'=>PHPWS_SOURCE_HTTP . $cssFile, 'relative'=>FALSE));
      return;
    }

    $themeFile = PHPWS_Template::getTplDir($module) . $filename;
    if (is_file($themeFile)){
      Layout::addToStyleList($cssFile);
      return;
    } elseif (FORCE_THEME_TEMPLATES)
	return;

    $cssFile = "templates/$module/$filename";      
    if (is_file($cssFile))
      Layout::addToStyleList($cssFile);

    return;
  }

  function addToStyleList($value){
    $alternate = FALSE;
    $title     = NULL;
    $relative  = TRUE;

    if (!is_array($value))
      $file = $value;
    else
      extract($value);

    $style = array("file"      =>$file,
		   "alternate" =>$alternate,
		   "title"     =>$title,
		   "relative"  =>$relative
		   );


    $GLOBALS['Style'][] = $style;
  }

  function alternateTheme($template, $module, $file){
    $theme = Layout::getTheme();
    Layout::loadStyleSheets();
    $result = PHPWS_Template::process($template, $module, $file);
    echo $result;
    exit();
  }

  function checkSettings(){
    if (!isset($_SESSION['Layout_Settings']))
      $_SESSION['Layout_Settings'] = & new Layout_Settings;
  }

  function createBox($module, $contentVar, $template){
    $tpl = new PHPWS_Template;
    
    $box = Layout::getBox($module, $contentVar);
    
    $file = $box->template;
    $directory = "themes/" . Layout::getCurrentTheme() . "/boxstyles/";
    if (isset($file) && is_file($directory . $file))
      $tpl->setFile($directory . $file, TRUE);
    else
      $tpl->setTemplate(DEFAULT_TEMPLATE);
    
    $tpl->setData($template);
    
    $content = $tpl->get();

    if (Layout::isMoveBox()){
      Layout::addStyle("layout");
      PHPWS_Core::initModClass("layout", "LayoutAdmin.php");
      $content .= Layout_Admin::moveBoxesTag($box);
    }

    return $content;
  }

  function clear($module, $contentVar){
    unset($GLOBALS['Layout'][$module][$contentVar]);
  }

  function disableFollow(){
    if (!isset($GLOBALS['Layout_Robots']))
      Layout::initLayout();

    switch ($GLOBALS['Layout_Robots']){
    case "01":
      $GLOBALS['Layout_Robots'] = "00";
      break;

    case "11":
      $GLOBALS['Layout_Robots'] = "10";
      break;
    }
  }

  function disableIndex(){
    if (!isset($GLOBALS['Layout_Robots']))
      Layout::initLayout();

    switch ($GLOBALS['Layout_Robots']){
    case "10":
      $GLOBALS['Layout_Robots'] = "00";
      break;

    case "11":
      $GLOBALS['Layout_Robots'] = "01";
      break;
    }
  }

  function display(){
    $themeVarList = array();
    $contentList = Layout::getBoxContent();

    // if content list is blank
    // 404 error?

    foreach ($contentList as $module=>$content){
      foreach ($content as $contentVar=>$template){
	if(!($theme_var = $_SESSION['Layout_Settings']->getBoxThemeVar($module, $contentVar)))
	  $theme_var = DEFAULT_THEME_VAR;

	if (!in_array($theme_var, $themeVarList))
	  $themeVarList[] = $theme_var;

	$order = $_SESSION['Layout_Settings']->getBoxOrder($module, $contentVar);

	if (empty($order))
	  $order = MAX_ORDER_VALUE;

	if (Layout::isBoxTpl($module, $contentVar)){
	  $unsortedLayout[$theme_var][$order] = Layout::createBox($module, $contentVar, $template);
	} else
	  $unsortedLayout[$theme_var][$order] = implode("", $template);
      }
    }

    if (isset($themeVarList)){
      foreach ($themeVarList as $theme_var){
	ksort($unsortedLayout[$theme_var]);
	$bodyLayout[strtoupper($theme_var)] = implode("", $unsortedLayout[$theme_var]);
      }
    } else
      $bodyLayout[] = implode("<br />", $unsortedLayout[$theme_var]);

    // Load body of theme 
    $finalTheme = &Layout::loadTheme(Layout::getCurrentTheme(), $bodyLayout);

    if (PEAR::isError($finalTheme))
      $content = implode("", $bodyLayout);
    else
      $content = $finalTheme->get();

    Layout::wrap(Layout::getCurrentTheme(), $content);
  }

  function getBox($module, $contentVar){
    return $_SESSION['Layout_Settings']->_boxes[$module][$contentVar];
  }

  function getContentVars(){
    Layout::checkSettings();
    return $_SESSION['Layout_Settings']->getContentVars();
  }

  function getCurrentTheme(){
    return $_SESSION['Layout_Settings']->current_theme;
  }

  function getBoxContent(){
    $list = NULL;
    if (!isset($GLOBALS['Layout']))
      return PHPWS_Error::get(LAYOUT_SESSION_NOT_SET, "layout", "getBoxContent");

    foreach ($GLOBALS['Layout'] as $module=>$content){
      foreach ($content as $contentVar=>$contentList){
	if (!is_array($contentList) || !isset($contentList['content']))
	  continue;
	
	foreach ($contentList['content'] as $tag=>$content)
	  $list[$module][$contentVar][strtoupper($tag)] = implode("", $content);
      }
    }
    return $list;
  }

  function getDefaultTheme(){
    return $_SESSION['Layout_Settings']->default_theme;
  }


  function getJavascript($directory, $data=NULL){
    if (isset($data) && !is_array($data))
      return PHPWS_Error::get();

    PHPWS_CORE::initCoreClass("File.php");
    $headfile    = "./javascript/$directory/head.js";
    $bodyfile    = "./javascript/$directory/body.js";
    $defaultfile = "./javascript/$directory/default.php";

    if (is_file($defaultfile))
      include $defaultfile;

    if (isset($default)){
      if (isset($data))
	$data = array_merge($default, $data);
      else
	$data = $default;
    }

    Layout::loadJavascriptFile($headfile, $directory, $data);

    if (is_file($bodyfile)){
      if (isset($data)){
	$tpl = new PHPWS_Template;
	$tpl->setFile($bodyfile, TRUE);
	$tpl->setData($data);
	
	$result = $tpl->get();
	if (!empty($result))
	  return $result;
	else
	  return file_get_contents($bodyfile);
      } else
	return file_get_contents($bodyfile);
    }

  }

  function getMetaRobot(){
    if (!isset($GLOBALS['Layout_Robots']))
      $meta_robots = "11";
    else
      $meta_robots = $GLOBALS['Layout_Robots'];

    switch ((string)$meta_robots){
    case "11":
      return "all";
      break;

    case "10":
      return "index, nofollow";
      break;

    case "01":
      return "noindex, follow";
      break;

    case "00":
      return "none";
      break;
    }
  }

  function getMetaTags(){
    extract($_SESSION['Layout_Settings']->getMetaTags());

    // Say it loud
    $metatags[] = "<meta name=\"generator\" content=\"phpWebSite\" />";

    if (!empty($author))
      $metatags[] = "<meta name=\"author\" content=\"$meta_author\" />";
    else
      $metatags[] = "<meta name=\"author\" content=\"phpWebSite\" />";

    if (!empty($meta_keywords))
      $metatags[] = "<meta name=\"keywords\" content=\"$meta_keywords\" />";

    if (!empty($meta_description))
      $metatags[] = "<meta name=\"description\" content=\"$meta_description\" />";
    
    if (!empty($meta_owner))
      $metatags[] = "<meta name=\"owner\" content=\"$meta_owner\" />";

    $robot = Layout::getMetaRobot();
    $metatags[] = "<meta name=\"robots\" content=\"$robot\" />";

    return implode("\n", $metatags);
  }

  function getOnLoad(){
    if (!isset($GLOBALS['Layout_Onload']))
      return NULL;

    return "onload=\"" . implode(" ", $GLOBALS['Layout_Onload']) ."\"";
  }

  function getStyleLinks($header=FALSE){
    foreach ($GLOBALS['Style'] as $link)
      $links[] = Layout::styleLink($link, $header);

    return implode("\n", $links);
  }

  function getTheme(){
    return $_SESSION['Layout_Settings']->current_theme;
  }

  function getThemeDir(){
    Layout::checkSettings();
    $themeDir = Layout::getTheme();
    return "themes/" . $themeDir . "/";
  }

  function isBoxTpl($module, $contentVar){
    if (isset($GLOBALS['Layout'][$module][$contentVar]))
      return $GLOBALS['Layout'][$module][$contentVar]['box'];
    else
      return NULL;
  }

  function isMoveBox(){
    return $_SESSION['Layout_Settings']->isMoveBox();
  }

  function loadJavascriptFile($filename, $index, $data=NULL){
    if (!is_file($filename))
      return FALSE;
    
    if (isset($data)){
      $tpl = new PHPWS_Template;
      $tpl->setFile($filename, TRUE);
      $tpl->setData($data);
      $result = $tpl->get();
      if (!empty($result))
	Layout::addJSHeader($result, $index);
      else
	Layout::addJSHeader(file_get_contents($filename), $index);
    } else
      Layout::addJSHeader(file_get_contents($filename), $index);
  }

  function loadModuleJavascript($module, $filename, $data=NULL){
    $directory = PHPWS_SOURCE_DIR . "mod/$module/javascript/$filename";

    if (!is_file($directory))
      return FALSE;
      
    return Layout::loadJavascriptFile($directory, $module, $data);
  }

  function loadStyleSheets(){
    $theme = Layout::getDefaultTheme();

    $directory = "./themes/$theme/";
    $file = $directory . "style.php";

    if (is_file($file)){
      include $file;

      if (isset($persistant))
	Layout::addToStyleList(array('file'=>$persistant));

      if (isset($default) && (isset($default['file']) && isset($default['title']))){
	Layout::addToStyleList(array('file'=>$default['file'], 'title'=>$default['title']));

	  if (isset($alternate) && is_array($alternate)){
	    foreach ($alternate as $altStyle){
	      if (isset($altStyle['file']) && isset($altStyle['title']))
		Layout::addToStyleList(array('file'=>$altStyle['file'],
					     'title'=>$altStyle['title'],
					     'alternate'=>TRUE
					     )
				       );	      
	    }
	  }

      }
    } else
      Layout::addToStyleList("style.css");
  }

  function &loadTheme($theme, $template){
    $tpl = new PHPWS_Template;
    $themeDir = Layout::getThemeDir();

    if (PEAR::isError($themeDir)){
      	PHPWS_Error::log($themeDir);
	PHPWS_Core::errorPage();
    }

    $result = $tpl->setFile($themeDir . "theme.tpl", TRUE);

    if (PEAR::isError($result))
      return $result;

    $template['THEME_DIRECTORY'] = "themes/$theme/";
    $tpl->setData($template);
    return $tpl;
  }

  function moveBoxes($key){
    $_SESSION['Layout_Settings']->_move_box = (bool)$key;
  }

  function reset(){
    $_SESSION['Layout_Settings'] = & new Layout_Settings;
  }

  function resetBoxes(){
    $_SESSION['Layout_Settings']->loadBoxes();
  }

  function set($text, $module, $contentVar, $box=TRUE){
    Layout::checkSettings();
    if (!isset($contentVar))
      $contentVar = DEFAULT_CONTENT_VAR;

    $box = (bool)$box;

    $GLOBALS['Layout'][$module][$contentVar]['content'] = NULL;
    Layout::add($text, $module, $contentVar, $box);
  }

  function styleLink($link, $header=FALSE){
    extract($link);
    $theme = Layout::getCurrentTheme();

    if ($relative){
      $directory = "./themes/$theme/$file";
    } else
      $directory = $file;

    if (!empty($title))
      $cssTitle = "title=\"$title\"";
    else
      $cssTitle = NULL;

    if ($header == TRUE){
      if ($alternate == TRUE)
	return "<?xml-stylesheet alternate=\"yes\" $cssTitle  href=\"$directory\" type=\"text/css\"?>";
      else
	return "<?xml-stylesheet $cssTitle href=\"$directory\" type=\"text/css\"?>";
    } else {
      if ($alternate == TRUE)
	return "<link rel=\"alternate stylesheet\" $cssTitle href=\"$directory\" type=\"text/css\" />";
      else
	return "<link rel=\"stylesheet\" $cssTitle href=\"$directory\" type=\"text/css\" />";
    }
  }

  function submitHeaders($theme, &$template){
    $testing = true;

    if($testing == FALSE && stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")){
      header("Content-Type: application/xhtml+xml; charset=UTF-8");
      $template["XML"] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
      $template["DOCTYPE"] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
      $template["XHTML"] = "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"" . CURRENT_LANGUAGE . "\">\n";
      $template["XML_STYLE"] = Layout::getStyleLinks(TRUE);
    } else {
      header("Content-Type: text/html; charset=UTF-8");
      $template["DOCTYPE"] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
      $template["XHTML"] = "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"" . CURRENT_LANGUAGE . "\" lang=\"" . CURRENT_LANGUAGE . "\">\n";
      $template['STYLE'] = Layout::getStyleLinks(FALSE);
    }
    header("Content-Language: " . CURRENT_LANGUAGE);
    header("Content-Script-Type: text/javascript");
    header("Content-Style-Type: text/css");
  }

  function wrap($theme, $content){
    if (isset($GLOBALS['Layout_JS'])){
      foreach ($GLOBALS['Layout_JS'] as $script=>$javascript)
	$jsHead[] = $javascript['head'];
      
      $template['JAVASCRIPT'] = implode("\n", $jsHead);
    }

    Layout::loadStyleSheets();
    Layout::submitHeaders($theme, $template);
    $template['METATAGS'] = Layout::getMetaTags();
    $template['PAGE_TITLE'] = $_SESSION['Layout_Settings']->page_title;
    $template['CONTENT'] = $content;
    $template['ONLOAD'] = Layout::getOnLoad();
    $result = PHPWS_Template::process($template, "layout", "header.tpl");

    echo $result;
  }


}
?>