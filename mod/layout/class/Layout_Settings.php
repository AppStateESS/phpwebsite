<?php

PHPWS_Core::initModClass('layout', 'Box.php');

class Layout_Settings {
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

  function getPageTitle()
  {
    if (isset($GLOBALS['Layout_Page_Title_Add'])) {
      return $this->page_title . PAGE_TITLE_DIVIDER . implode(PAGE_TITLE_DIVIDER, $GLOBALS['Layout_Page_Title_Add']);
    } else {
      return $this->page_title;
    }
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
    $db = new PHPWS_db('layout_box');
    $db->addWhere('theme', $theme);
    if(!$boxes = $db->getObjects('Layout_Box'))
      return;

    foreach ($boxes as $box)
      $final[$box->module][$box->content_var] = $box;

    $this->_boxes = $final;
  }


  function loadContentVars(){
    $db = new PHPWS_db('layout_box');
    $db->addWhere('theme', $this->current_theme);
    $db->addColumn('content_var');
    $result = $db->select('col');

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }
    
    if (empty($result))
      return;

    $this->_contentVars = $result;
  }

  function loadSettings(){
    $db = new PHPWS_DB('layout_config');
    $result = $db->loadObject($this);

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      PHPWS_Core::errorPage();
    }

    if (empty($this->current_theme))
      $this->current_theme = $this->default_theme;

    $themeInit = './themes/' . $this->current_theme . '/theme.ini';
    $theme_variables[] = DEFAULT_THEME_VAR;
    $theme_variables[] = DEFAULT_BOX_VAR;

    if (is_file($themeInit)){
      $themeVars = parse_ini_file($themeInit, TRUE);
      if (isset($themeVars['theme_variables'])) {
	$theme_variables = array_merge($theme_variables, $themeVars['theme_variables']);
      }
    }

    $this->_theme_variables = $theme_variables;
  }

  function saveSettings(){
    $db = & new PHPWS_DB('layout_config');
    $vars = PHPWS_Core::stripObjValues($this);
    unset($vars['_contentVars']);
    unset($vars['_boxes']);
    unset($vars['_box_order']);
    unset($vars['_move_box']);
    unset($vars['_theme_variables']);
    unset($vars['current_theme']);
    $db->addValue($vars);
    return $db->update();
  }

}

?>