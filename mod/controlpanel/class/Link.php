<?php

class PHPWS_Panel_Link {
  var $id;
  var $label;
  var $active;
  var $module;
  var $itemname;
  var $restricted;
  var $tab;
  var $url;
  var $description;
  var $image;
  var $link_order;

  function PHPWS_Panel_Link($id=NULL){
    if (!isset($id))
      return;

    $result = $this->init($id);
    if (PEAR::isError($result))
      PHPWS_Error::log($result);
  }

  function init($id){
    $db = & new PHPWS_DB("controlpanel_link");
    $db->addWhere("id", $id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setTab($tab){
    $this->tab = $tab;
  }

  function getTab(){
    return $this->tab;
  }

  function setActive($active){
    $this->active = (bool)$active;
  }

  function getActive(){
    return $this->active;
  }

  function setLabel($label){
    $this->label = $label;
  }

  function getLabel(){
    return $this->label;
  }


  function getDescription(){
    return $this->description;
  }

  function setDescription($description){
    $this->description = $description;
  }


  function setImage($image){
    $this->image = $image;
  }

  function getImage($tag=FALSE, $linkable=FALSE){
    if ($tag == FALSE)
      return $this->image;

    $image = "<img src=\"" . $this->image . "\" border=\"0\" alt=\"" . $this->getLabel() . "\"/>";

    if ($linkable == TRUE)
      $image = "<a href=\"" . $this->url . "\">" . $image . "</a>";

    return $image;
  }

  function setUrl($url){
    $this->url = $url;
  }
  
  function getUrl($tag=FALSE){
    if ($tag)
      return "<a href=\"" . $this->url . "\">" . $this->getLabel() . "</a>";
    else
      return $this->url;
  }

  function setLinkOrder($order){
    $this->link_order = (int)$order;
  }

  function getLinkOrder(){
    if (isset($this->link_order))
      return $this->link_order;

    $DB = @ new PHPWS_DB("controlpanel_link");
    $DB->addWhere('tab', $this->getTab());
    $DB->addColumn('link_order');
    $max = $DB->select("max");
    
    if (PEAR::isError($max))
      return $max;

    if (isset($max))
      return $max + 1;
    else
      return 1;
  }


  function setModule($module){
    $this->module = $module;
  }

  function getModule(){
    return $this->module;
  }

  function setItemName($itemname){
    $this->itemname = $itemname;
  }

  function getItemName(){
    return $this->itemname;
  }

  function isRestricted(){
    return (bool)$this->restricted;
  }

  function setRestricted($restrict){
    $this->restricted = $restrict;
  }

  function save(){
    $db = & new PHPWS_DB("controlpanel_link");
    if (isset($this->id))
      $db->addWhere("id", $this->id);

    $this->link_order = $this->getLinkOrder();

    $result = $db->saveObject($this);
    return $result;
  }

  function view(){
    $tpl['IMAGE']       = $this->getImage(TRUE, TRUE);
    $tpl['NAME']        = $this->getUrl(TRUE);
    $tpl['DESCRIPTION'] = $this->getDescription();

    return PHPWS_Template::process($tpl, "controlpanel", "link.tpl");
  }

  function kill(){
    $db = & new PHPWS_DB("controlpanel_link");
    $db->addWhere("id", $this->getId());
    $result = $db->delete();
    if (PEAR::isError($result))
      return $result;

    $tab = $this->getTab();
    
    $db->reset();
    $db->addWhere("tab", $tab);
    $db->addOrder("link_order");
    $result = $db->getObjects("PHPWS_Panel_Link");

    if (PEAR::isError($result))
      return $result;

    if (empty($result))
      return TRUE;

    $count = 1;
    foreach ($result as $link){
      $link->setLinkOrder($count);
      $link->save();
      $count++;
    }
  }

}
?>