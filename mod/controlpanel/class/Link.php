<?php

class PHPWS_ControlPanel_Link extends PHPWS_Item{
  var $_id;
  var $_label;
  var $_active;
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

  function getDescription(){
    return $this->_description;
  }

  function setImage($image){
    $this->_image = $image;
  }

  function getImage(){
    return $this->_image;
  }

  function setUrl($url){
    $this->_url = $url;
  }
  
  function getUrl(){
    return $this->_url;
  }

}
?>