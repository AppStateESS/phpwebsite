<?php

PHPWS_Core::initModClass("layout", "Box.php");

class Layout_Settings{
  var $current_theme    = NULL;
  var $default_theme  	= NULL;
  var $userAllow  	= 0;
  var $page_title       = NULL;
  var $meta_keywords  	= NULL;
  var $meta_description = NULL;
  var $meta_robots  	= NULL;
  var $meta_owner  	= NULL;
  var $meta_author      = NULL;
  var $meta_content     = NULL;
  var $header           = NULL;
  var $footer           = NULL;
  var $cache            = TRUE;
  var $_contentVars     = array();
  var $_boxes           = array();
  var $_box_order       = array();
  var $_move_box        = FALSE;
  var $_theme_variables = NULL;



  function Layout_Settings(){
    $this->loadSettings();
    $this->loadContentVars();
    $this->loadBoxes();
    $GLOBALS['Layout_Robots'] = $this->meta_robots;
  }

  function getBoxThemeVar($module, $contentVar){
    if (isset($this->_boxes[$module][$contentVar]))
      return $this->_boxes[$module][$contentVar]->getThemeVar();
    else
      return FALSE;
  }

  function getBoxOrder($module, $contentVar){
    if (isset($this->_boxes[$module][$contentVar]))
      return $this->_boxes[$module][$contentVar]->getBoxOrder();
    else
      return FALSE;
  }

  function getContentVars(){
    return $this->_contentVars();
  }

  function getMetaTags(){
    $meta['meta_author']      = $this->meta_author;
    $meta['meta_keywords']    = $this->meta_keywords;
    $meta['meta_description'] = $this->meta_description;
    $meta['meta_owner']       = $this->meta_owner;
    $meta['meta_robots']      = $this->meta_robots;
    $meta['page_title']       = $this->page_title;

    return $meta;
  }

  function getThemeVariables(){
    return $this->_theme_variables;
  }

  function isContentVar($contentVar){
    return in_array($contentVar, $this->_contentVars);
  }

  function isMoveBox(){
    return (bool)$this->_move_box;
  }
  
  function loadBoxes(){
    $theme = $this->current_theme;
    $db = new PHPWS_db("layout_box");
    $db->addWhere("theme", $theme);
    if(!$boxes = $db->getObjects("Layout_Box"))
      return;

    foreach ($boxes as $box)
      $final[$box->module][$box->content_var] = $box;

    $this->_boxes = $final;
  }


  function loadContentVars(){
    $db = new PHPWS_db("layout_box");
    $db->addWhere("theme", $this->current_theme);
    $db->addColumn("content_var");
    $db->setindexBy("module");
    $result = $db->select("col");
    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }
    
    if (empty($result))
      return;

    $this->_contentVars = $result;
  }

  function loadSettings(){
    require_once("File.php");
    $db = new PHPWS_DB("layout_config");
    $result = $db->loadObject($this);

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }

    if (empty($this->current_theme))
      $this->current_theme = $this->default_theme;

    $transferFile = "./themes/" . $this->current_theme . "/transfers.tpl";

    if (is_file($transferFile)){
      $themeVars = explode("\n", trim(File::readAll($transferFile)));
      $this->_theme_variables = $themeVars;
    } else
      $this->_theme_variables = array(DEFAULT_THEME_VAR);
  }

  function saveSettings(){
    $db = & new PHPWS_DB("layout_config");
    $db->setQWhere("1");
    return $db->saveObject($this);
  }

}

?>