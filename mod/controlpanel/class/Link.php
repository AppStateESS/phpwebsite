<?php

class PHPWS_Panel_Link {
  var $_id;
  var $_label;
  var $_active;
  var $_module;
  var $_itemname;
  var $_restricted;
  var $_tab;
  var $_url;
  var $_description;
  var $_image;
  var $_link_order;

  function PHPWS_Panel_Link($id=NULL){
    if (isset($id))
      $this->init($id);
  }

  function init($id){
    $db = & new PHPWS_DB("controlpanel_link");
    $db->addWhere("id", $id);
    $result = $db->loadObjects("PHPWS_Panel_Link", TRUE);
    if (PEAR::isError($result))
      return $result;
    else
      $this = $result;
  }

  function setTab($tab){
    $this->_tab = $tab;
  }

  function getTab(){
    return $this->_tab;
  }

  function setActive($active){
    $this->_active = (bool)$active;
  }

  function getActive(){
    return $this->_active;
  }

  function setLabel($label){
    $this->_label = $label;
  }

  function getLabel(){
    return $this->_label;
  }


  function getDescription(){
    return $this->_description;
  }

  function setDescription($description){
    $this->_description = $description;
  }


  function setImage($image){
    $this->_image = $image;
  }

  function getImage($tag=FALSE, $linkable=FALSE){
    if ($tag == FALSE)
      return $this->_image;

    $image = "<img src=\"" . $this->_image . "\" border=\"0\" alt=\"" . $this->getLabel() . "\"/>";

    if ($linkable == TRUE)
      $image = "<a href=\"" . $this->_url . "\">" . $image . "</a>";

    return $image;
  }

  function setUrl($url){
    $this->_url = $url;
  }
  
  function getUrl($tag=FALSE){
    if ($tag)
      return "<a href=\"" . $this->_url . "\">" . $this->getLabel() . "</a>";
    else
      return $this->_url;
  }

  function getLinkOrder(){
    if (isset($this->_link_order))
      return $this->_link_order;

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
    $this->_module = $module;
  }

  function getModule(){
    return $this->_module;
  }

  function setItemName($itemname){
    $this->_itemname = $itemname;
  }

  function getItemName(){
    return $this->_itemname;
  }

  function isRestricted(){
    return (bool)$this->_restricted;
  }

  function setRestricted($restrict){
    $this->_restricted = $restrict;
  }

  function save(){

    $db = & new PHPWS_DB("controlpanel_link");
    if (isset($this->_id))
      $db->addWhere("id", $this->_id);

    $this->_link_order = $this->getLinkOrder();

    $result = $db->saveObject($this, TRUE);
    return $result;

  }

  function view(){
    $tpl['IMAGE']       = $this->getImage(TRUE, TRUE);
    $tpl['NAME']        = $this->getUrl(TRUE);
    $tpl['DESCRIPTION'] = $this->getDescription();

    return PHPWS_Template::process($tpl, "controlpanel", "link.tpl");
  }

}
?>