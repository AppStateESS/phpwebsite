<?php
/**
 * Tab class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

class PHPWS_Panel_Tab {
  var $_id          = NULL;
  var $_title       = NULL;
  var $_label       = NULL;
  var $_link        = NULL;
  var $_tab_order   = NULL;
  var $_color       = NULL;
  var $_itemname    = NULL;
  var $_style       = NULL;

  function PHPWS_Panel_Tab($id=NULL) {

    if(isset($id)) {
      $this->setId($id);
      $this->init();
    }
  }

  function setId($id){
    $this->_id = $id;
  }

  function init(){
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("id", $this->getId());
    $result = $DB->select("row");

    foreach ($result as $key=>$value)
      $this->{'_' . $key} = $value;

  }

  function getId(){
    return $this->_id;
  }

  function setTitle($title){
    $this->_title = strip_tags($title);
  }

  function getTitle($noBreak=TRUE){
    if ($noBreak)
      return str_replace(" ", "&nbsp;", $this->_title);
    else
      return $this->_title;
  }

  function setLabel($label){
    $this->_label = $label;
  }

  function getLabel(){
    return $this->_label;
  }

  function setLink($link){
    $this->_link = $link;
  }

  function getLink($addTitle=TRUE){
    if ($addTitle){
      $title = $this->getTitle();
      $link = $this->getLink(FALSE);
      return "<a href=\"$link" . "&amp;tab=" . $this->getId() . "\">$title</a>";
    } else
      return $this->_link;
  }


  function setOrder($order){
    $this->_tab_order = $order;
  }

  function getOrder(){
    if (isset($this->_tab_order))
      return $this->_tab_order;

    $DB = @ new PHPWS_DB("controlpanel_tab");
    $DB->addWhere('itemname', $this->getItemname());
    $DB->addColumn('tab_order');
    $max = $DB->select("max");
    
    if (PEAR::isError($max))
      exit($max->getMessage());

    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  function setColor($color){
    $this->_color = $color;
  }

  function getColor(){
    return $this->_color;
  }
  
  function setStyle($style){
    $this->_style = $style;
  }

  function getStyle(){
    return $this->_style;
  }


  function setItemname($itemname){
    $this->_itemname = $itemname;
  }

  function getItemname(){
    return $this->_itemname;
  }

  function save(){
    // MUST HAVE ITEMNAME!
    $DB = @ new PHPWS_DB("controlpanel_tab");

    $id                   = $this->getId();
    $save['title']        = $this->getTitle(FALSE);
    $save['label']        = $this->getLabel();
    $save['link']         = $this->getLink(FALSE);
    $save['color']        = $this->getColor();
    $save['itemname']     = $this->getItemname();
    $save['style']        = $this->getStyle();
    $save['tab_order']    = $this->getOrder();

    foreach ($save as $key=>$value)
      if (is_null($value))
	unset ($save[$key]);

    $DB->addValue($save);

    if (isset($id)){
      $DB->addWhere("id", $id);
      $result = $DB->update();
    } else {
      $result = $DB->insert();
      if (is_numeric($result))
	$this->setId($result);
    }

    return $result;
  }

  function nextBox(){

    $DB->addWhere("theme", $this->getTheme());
    $DB->addWhere("theme_var", $this->getThemeVar());
    $DB->addColumn("box_order");
    $max = $DB->select("max");
    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  function kill(){
    $db = & new PHPWS_DB("controlpanel_tab");
    $db->addWhere("id", $this->getId());
    $result = $db->delete();
    if (PEAR::isError($result))
      return $result;

    $db->reset();
    $db->addOrder("tab_order");
    $result = $db->loadObjects("PHPWS_Panel_Tab");

    if (PEAR::isError($result))
      return $result;

    if (empty($result))
      return TRUE;

    $count = 1;
    foreach ($result as $tab){
      $tab->setOrder($count);
      $tab->save();
      $count++;
    }

  }

}

?>