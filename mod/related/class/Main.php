<?php

PHPWS_Core::initModClass("related", "Friend.php");

class Related {

  var $id        = NULL;
  var $main_id   = NULL;
  var $module    = NULL;
  var $item_name = NULL;
  var $title     = NULL;
  var $url       = NULL;
  var $rating    = NULL;
  var $active    = FALSE;
  var $friends   = NULL;

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function setMainId($main_id){
    $this->main_id = $main_id;
  }

  function getMainId(){
    return $this->main_id;
  }

  function setModule($module){
    $this->module = $module;
  }

  function getModule(){
    return $this->module;
  }

  function setItemName($item_name){
    $this->item_name = $item_name;
  }

  function getItemName(){
    return $this->item_name;
  }


  function setTitle($title){
    $this->title = preg_replace("/[^\w\s]/", "", strip_tags($title));
  }

  function getTitle(){
    return $this->title;
  }

  function setUrl($url){
    $this->url = $url;
  }

  function getUrl(){
    return $this->url;
  }

  function setRating($rating){
    $this->rating = (int)$rating;
  }

  function getRating(){
    return $this->rating;
  }

  function setActive($active){
    $this->active = (bool)$active;
  }

  function isActive(){
    return $this->active;
  }

  function setFriends($friends){
    $this->friends = $friends;
  }

  function getFriends(){
    return $this->friends();
  }

  function addFriend($friend){
    $this->friends[$friend->getRating()] = $friend;
  }


  function loadFriends(){
    if (!isset($this->id))
      return NULL;

    $db = & new PHPWS_DB("related_friends");
    $db->addWhere("source_id", $this->id);
    $db->addOrder("rating");
    $db->setIndexBy("rating");
    $result = $db->select("col");

    if (PEAR::isError($result))
      return $result;

    $this->friends = $result;
  }


}

?>