<?php
/**
 * Tab class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

class PHPWS_Panel_Tab {
  var $id          = NULL;
  var $title       = NULL;
  var $link        = NULL;
  var $tab_order   = NULL;
  var $itemname    = NULL;

  function PHPWS_Panel_Tab($id=NULL) {

    if(isset($id)) {
      $this->setId($id);
      $this->init();
    }
  }

  function setId($id){
    $this->id = $id;
  }

  function init(){
    $DB = new PHPWS_DB("controlpanel_tab");
    $DB->addWhere("id", $this->getId());
    $DB->loadObject($this);
  }

  function getId(){
    return $this->id;
  }

  function setTitle($title){
    $this->title = strip_tags($title);
  }

  function getTitle($noBreak=TRUE){
    if ($noBreak)
      return str_replace(" ", "&nbsp;", $this->title);
    else
      return $this->title;
  }

  function setLink($link){
    $this->link = $link;
  }

  function getLink($addTitle=TRUE){
    if ($addTitle){
      $title = $this->getTitle();
      $link = $this->getLink(FALSE);
      return "<a href=\"$link" . "&amp;tab=" . $this->getId() . "\">$title</a>";
    } else
      return $this->link;
  }


  function setOrder($order){
    $this->tab_order = $order;
  }

  function getOrder(){
    if (isset($this->tab_order))
      return $this->tab_order;

    $DB = & new PHPWS_DB("controlpanel_tab");
    $DB->addColumn('tab_order');
    $max = $DB->select("max");
    
    if (PEAR::isError($max))
      exit($max->getMessage());

    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  function setItemname($itemname){
    $this->itemname = $itemname;
  }

  function getItemname(){
    return $this->itemname;
  }

  function save(){
    $db = & new PHPWS_DB("controlpanel_tab");
    $db->addWhere("id", $this->id);
    $db->delete();
    $db->resetWhere();
    $this->tab_order = $this->getOrder();
    return $db->saveObject($this);
  }

  function nextBox(){
    $db = & new PHPWS_DB("controlpanel_tab");
    $db->addWhere("theme", $this->getTheme());
    $db->addWhere("theme_var", $this->getThemeVar());
    $db->addColumn("box_order");
    $max = $db->select("max");
    if (isset($max))
      return $max + 1;
    else
      return 1;
  }

  /*
  function kill(){
    $db = & new PHPWS_DB("controlpanel_tab");
    $db->addWhere("id", $this->getId());
    $result = $db->delete();
    if (PEAR::isError($result))
      return $result;

    $db->reset();
    $db->addOrder("tab_order");
    $result = $db->getObjects("PHPWS_Panel_Tab");

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
  */
}

?>