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


class oldCore extends oldDB{
  var $home_dir;

  function oldCore(){
    $this->home_dir = "";
  }

  function moduleExists($module){
    PHPWS_Core::moduleExists($module);
  }

}


class oldDB{

  function sqlImport($filename, $write=TRUE, $suppress_error=FALSE){
    PHPWS_Core::initCoreClass("File.php");
    $text = PHPWS_File::readFile($filename);
    return PHPWS_DB::import($text);
  }

  function sqlSelect($table_name, $match_column=NULL, $match_value=NULL, $order_by=NULL, $compare=NULL, $and_or=NULL, $limit=NULL, $mode=NULL, $test=FALSE) {
    $sql = & new PHPWS_DB($table_name);
    if (isset($match_column)){
      if (is_array($match_column)){
	foreach ($match_column as $columnName=>$columnValue){
	  $operator = $conj = NULL;

	  if (is_array($compare) && isset($compare[$columnName]))
	    $operator = $compare[$columnName];
	  
	  if (is_array($and_or) && isset($and_or[$columnName]))
	    $conj = $and_or[$columnName];
	  
	  $sql->addWhere($columnName, $columnValue, $operator, $conj);
	}
      } else {
	$sql->addWhere($match_column, $match_value, $order_by, $compare);
      }
    }

    return $sql->select();
  }

  function getCol($sql){
    return PHPWS_DB::select("col", $sql);
  }

  function getAll($sql){
    return PHPWS_DB::select("all", $sql);
  }


}

class oldLayout {
  var $current_theme;

  function oldLayout(){
    $current_theme = Layout::getTheme();
  }

}


class PHPWS_Crutch {

  function initializeModule($module){
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
      $_SESSION['OBJ_user'] = & $_SESSION['User'];

    if (!isset($_SESSION['translate']))
      $_SESSION['translate'] = & new oldTranslate;

    if (!isset($_SESSION['OBJ_layout']))
      $_SESSION['OBJ_layout'] = & new oldLayout;

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
	  Layout::add($GLOBALS[$layout['content_var']], $layout['content_var']);
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