<?php

// If gettext is not compiled into your php installation, this function
// becomes needed.
/* revisit
function _($text){
  return $text;
}
*/
/**
 * Pre-094 code
 */

define("PHPWS_TBL_PREFIX", TABLE_PREFIX);
define("PHPWS_HOME_DIR" , "./");
PHPWS_Core::initCoreClass("WizardBag.php");
PHPWS_Core::initCoreClass("Crutch_Form.php");
PHPWS_Core::initCoreClass("Crutch_DB.php");
PHPWS_Core::initModClass("help", "Help.php");

class CLS_Help extends PHPWS_Help{}

class oldCore extends oldDB{
  var $home_dir = NULL;
  var $datetime = NULL;

  function oldCore(){
    $this->home_dir = "";
    $this->datetime = new PHPWS_DateTime;
  }

  function moduleExists($module){
    PHPWS_Core::moduleExists($module);
  }

}

class PHPWS_DateTime{
  var $date_month;
  var $date_day;
  var $date_year;
  var $day_mode;
  var $day_start;
  var $date_order;
  var $time_format;
  var $time_dif;

  function PHPWS_DateTime(){
    $this->date_month  = "m";
    $this->date_day    = "d";
    $this->date_year   = "Y";
    $this->day_mode    = "l";
    $this->day_start   = PHPWS_DAY_START;
    $this->time_dif    = PHPWS_TIME_DIFF * 3600;
    
    // Deprecated.  Use above defines
    $this->date_order  = PHPWS_DATE_FORMAT;
    $this->time_format = PHPWS_TIME_FORMAT;
  }
}


class oldLayout {
  var $current_theme;

  function oldLayout(){
    $current_theme = Layout::getTheme();
  }

}


class PHPWS_Crutch {

  function setModule(){
    if (isset($_REQUEST['module']))
      $GLOBALS['module'] = $_REQUEST['module'];
    else
      $GLOBALS['module'] = "home";
  }

  function initializeModule($module){
    PHPWS_Crutch::setModule();

    $includeFile = PHPWS_SOURCE_DIR . "mod/" . $module . "/conf/boost.php";
    include($includeFile);
    if (isset($mod_class_files) && is_array($mod_class_files)){
      foreach ($mod_class_files as $requireMe)
	PHPWS_Core::initModClass($module, $requireMe);
    }

    if (isset($mod_sessions) && (isset($init_object))){
      foreach ($mod_sessions as $sessionName){
	if (isset($init_object[$sessionName]) && !isset($_SESSION[$sessionName]))
	  $_SESSION[$sessionName] = & new $init_object[$sessionName];
      }
    }
  }

  function startSessions(){
    if (!isset($_SESSION['OBJ_user']))
      $_SESSION['OBJ_user'] = $_SESSION['User'];

    if (!isset($_SESSION['translate']))
      $_SESSION['translate'] = & new oldTranslate;

    if (!isset($_SESSION['OBJ_layout']))
      $_SESSION['OBJ_layout'] = & new oldLayout;

    $GLOBALS['Crutch_Session_Started'] = TRUE;

  }

  function closeSessions(){
    PHPWS_Core::killSession("OBJ_user");
    PHPWS_Core::killSession("translate");
    PHPWS_Core::killSession("OBJ_layout");
  }

  function getOldLayout(){
    if (!isset($GLOBALS['pre094_modules']) || !is_array($GLOBALS['pre094_modules']))
      return;

    foreach ($GLOBALS['pre094_modules'] as $module){
      $file = PHPWS_SOURCE_DIR . "mod/$module/conf/layout.php";
      if (!is_file($file))
	continue;

      include $file;

      if (!isset($layout_info))
	continue;

      foreach ($layout_info as $layout){
	if (isset($GLOBALS[$layout['content_var']]))
	  Layout::add($GLOBALS[$layout['content_var']], $module, $layout['content_var']);
      }
    }
  }

}

class oldTranslate {
  var $test = 1;
  function it($phrase, $var1=NULL, $var2=NULL, $var3=NULL){
    $phrase = str_replace("[var1]", $var1, $phrase);
    $phrase = str_replace("[var2]", $var2, $phrase);
    $phrase = str_replace("[var3]", $var3, $phrase);

    return $phrase;
  }
}



$GLOBALS['core'] = & new oldCore;
if (isset($_REQUEST['module'])){
  $GLOBALS['module'] = $_REQUEST['module'];
}

PHPWS_Core::initModClass("help", "Help.php");

?>