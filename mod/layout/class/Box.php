<?php

class Layout_Box {
  var $_id          = NULL;
  var $_theme       = NULL; 
  var $_content_var = NULL;
  var $_theme_var   = NULL;
  var $_template    = NULL;
  var $_box_order       = NULL;

  function Layout_Box($id=NULL){
    if (isset($id))
      $this->load($id);
  }

  function load($id){
    $DB = new PHPWS_DB("layout_box");
    $DB->addWhere("id", $id);
    $result = $DB->select("row");
    $this->setID($id);
    $this->setTheme($result['theme']);
    $this->setBoxOrder($result['box_order']);
    $this->setContentVar($result['content_var']);
    $this->setThemeVar($result['theme_var']);
    $this->setTemplate($result['template']);
  }

  function setID($id){
    $this->_id = $id;
  }

  function getID(){
    return $this->_id;
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

  function getBoxOrder(){
    return $this->_box_order;
  }

  function setBoxOrder($order){
    $this->_box_order = $order;
  }

  function save(){
    $DB = new PHPWS_DB("layout_box");
    $DB->addValue("theme", $this->getTheme());
    $DB->addValue("content_var", $this->getContentVar());
    $DB->addValue("theme_var", $this->getThemeVar());
    $DB->addValue("template", $this->getTemplate());

    $box_order = $this->getBoxOrder();
    if (!isset($box_order))
      $box_order = $this->nextBox();
    
    $DB->addValue("box_order", $box_order);

    $DB->addValue("active", 1);

    if (isset($this->_id)){
      $DB->addWhere("id", $this->getID());
      return $DB->update();
    }
    else {

      $result = $DB->insert();
      if (PEAR::isError($result))
	return $result;
      else {
	$this->setID($result);
	return TRUE;
      }
    }
    
  }

  function moveUp(){
    $db = & new PHPWS_DB("layout_box");
    $db->addWhere("id", $this->getID(), "!=");
    $db->addWhere("theme", $this->getTheme());
    $db->addWhere("theme_var", $this->getThemeVar());
    $db->setIndexBy("box_order");
    $boxes = $db->loadObjects("Layout_Box");

    if (!isset($boxes))
      return;

    $db->addColumn("box_order");
    $max = $db->select("max");
    $oldOrder = $this->getBoxOrder();
    $newOrder = $oldOrder - 1;

    if ($oldOrder == 1){
      $this->setBoxOrder($max + 1);
      $this->save();
    }
    else {
      $this->setBoxOrder($newOrder);
      $this->save();
      $boxes[$newOrder]->setBoxOrder($oldOrder);
      $boxes[$newOrder]->save();
    }
  }

  function moveDown(){
    $db = & new PHPWS_DB("layout_box");
    $db->addWhere("id", $this->getID(), "!=");
    $db->addWhere("theme", $this->getTheme());
    $db->addWhere("theme_var", $this->getThemeVar());
    $db->setIndexBy("box_order");
    $boxes = $db->loadObjects("Layout_Box");

    if (!isset($boxes))
      return;

    $db->addColumn("box_order");
    $max = $db->select("max");
    $oldOrder = $this->getBoxOrder();
    $newOrder = $oldOrder + 1;

    if ($oldOrder == ($max + 1)){
      $this->setBoxOrder(0);
      $this->save();
    }
    else {
      $this->setBoxOrder($newOrder);
      $this->save();
      $boxes[$newOrder]->setBoxOrder($oldOrder);
      $boxes[$newOrder]->save();
    }
  }


  function reorderBoxes($theme, $themeVar){
    $db = & new PHPWS_DB("layout_box");
    $db->addWhere("theme", $theme);
    $db->addWhere("theme_var", $themeVar);
    $db->addOrder("box_order");
    $boxes = $db->loadObjects("Layout_Box");

    if (!isset($boxes))
      return;

    $count = 1;
    foreach ($boxes as $box){
      $box->setBoxOrder($count);
      $box->save();
      $count++;
    }
  }

  function nextBox(){
    $DB = new PHPWS_DB("layout_box");
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
      $includeFile =  Layout::getThemeDir() . "config.php";
      
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