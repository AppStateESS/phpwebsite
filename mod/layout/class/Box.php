<?php

class PHPWS_Layout_Box {
  var $_id          = NULL;
  var $_theme       = NULL; 
  var $_content_var = NULL;
  var $_theme_var   = NULL;
  var $_template    = NULL;
  var $_box_order       = NULL;

  function PHPWS_Layout_Box($id=NULL){
    if (isset($id))
      $this->load($id);
  }

  function load($id){
    $DB = new PHPWS_DB("mod_layout_box");
    $DB->addWhere("id", $id);
    $result = $DB->select("row");
    $this->setTheme($result['theme']);
    $this->setContentVar($result['content_var']);
    $this->setThemeVar($result['theme_var']);
    $this->setTemplate($result['template']);
  }


  function setTheme($theme){
    $this->_theme = $theme;
  }

  function setContentVar($content_var){
    $this->_content_var = $content_var;
  }

  function setThemeVar($theme_var){
    $this->_theme_var = $theme_var;
  }

  function setTemplate($template){
    $this->_template = $template;
  }

  function getTheme(){
    return $this->_theme;
  }

  function getContentVar(){
    return $this->_content_var;
  }

  function getThemeVar(){
    return $this->_theme_var;
  }

  function getTemplate(){
    return $this->_template;
  }


  function save(){
    $DB = new PHPWS_DB("mod_layout_box");
    $DB->addValue("theme", $this->getTheme());
    $DB->addValue("content_var", $this->getContentVar());
    $DB->addValue("theme_var", $this->getThemeVar());
    $DB->addValue("template", $this->getTemplate());
    $DB->addValue("box_order", $this->nextBox());
    $DB->addValue("active", 1);
    return $DB->insert();
    
  }

  function nextBox(){
    $DB = new PHPWS_DB("mod_layout_box");
    $DB->addWhere("theme", $this->getTheme());
    $DB->addWhere("theme_var", $this->getThemeVar());
    $DB->addColumn("box_order");
    $max = $DB->select("max");
    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  function setDefaultTemplate(){
    static $configExists = 0;
    static $theme_box = NULL;
    
    $contentVar = $this->getContentVar();
    
    if ($configExists == -1)
      return NULL;

    if ($configExists == 0){
      $includeFile =  PHPWS_Layout::getThemeDir() . "config.php";
      
      if (!is_file($includeFile)){
	$configExists = -1;
	return NULL;
      }
      include $includeFile;
      
      if (!isset($theme_box)){
	$configExists = -1;
	return NULL;
      }
    }
    
    $configExists = 1;
    
    if (isset($theme_box[$contentVar]))
      $this->setTemplate($theme_box[$contentVar]);
    elseif (isset($theme_box['default']))
      $this->setTemplate($theme_box['default']);
    else
      $this->setTemplate(NULL);
  }

  
}
?>