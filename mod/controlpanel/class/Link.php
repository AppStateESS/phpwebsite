<?php

class PHPWS_ControlPanel_Link extends PHPWS_Item{
  var $_id;
  var $_label;
  var $_module;
  var $_itemName;
  var $_tab;
  var $_url;
  var $_description;
  var $_image;
  var $_link_order;

  function PHPWS_ControlPanel_Link($id=NULL){
    $exclude = array ("_owner",
		      "_editor",
		      "_ip");

    $this->addExclude($exclude);
    $this->setTable("controlpanel_link");
  }

  function setTab($tab){
    $this->_tab = $tab;
  }


  function getTab(){
    return $this->_tab;
  }

  function getDescription(){
    return $this->_description;
  }

  function setImage($image){
    $this->_image = $image;
  }

  function getImage($tag=FALSE, $linkable=FALSE){
    if ($tag){
      PHPWS_Core::initCoreClass("Image.php");
      $image = new PHPWS_Image($this->_image);
      if ($linkable)
	return "<a href=\"" . $this->getUrl() . "\">" . $image->getTag() . "</a>";
      else
	return $image->getTag();
    } else
      return $this->_image;
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

  function setModule($module){
    $this->_module = $module;
  }

  function getModule(){
    return $this->_module;
  }

  function setItemName($itemname){
    $this->_itemName = $itemName;
  }

  function getItemName(){
    return $this->_itemName;
  }

}
?>