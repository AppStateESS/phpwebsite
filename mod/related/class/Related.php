<?php

PHPWS_Core::configRequireOnce("related", "config.php");
PHPWS_Core::initModClass("related", "Action.php");


class Related {

  var $id        = NULL;
  var $main_id   = NULL;
  var $module    = NULL;
  var $item_name = NULL;
  var $title     = NULL;
  var $url       = NULL;
  var $active    = TRUE;
  var $friends   = NULL;
  var $_banked   = FALSE;
  var $_current  = NULL;


  function Related($id=NULL){
    if (empty($id))
      return;

    $this->setId($id);
    $result = $this->init();
    if (PEAR::isError($result))
      PHPWS_Error::log($result);
  }

  function init(){
    $db = & new PHPWS_DB("related_main");
    $db->addWhere("id", $this->id);
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

  function getItemName($sub=FALSE){
    if ($sub == TRUE && empty($this->item_name))
      return $this->getModule();
    else
      return $this->item_name;
  }


  function setTitle($title){
    $this->title = preg_replace("/[^" . ALLOWED_TITLE_CHARS . "]/", "", strip_tags($title));
  }

  function getTitle(){
    return $this->title;
  }

  function setUrl($url){
    $this->url = $url;
  }

  function getUrl($clickable=FALSE){
    if ($clickable)
      return "<a href=\"" . $this->url . "\">" . $this->title . "</a>";
    else
      return $this->url;
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
    return $this->friends;
  }

  function addFriend($friend){
    $this->friends[] = $friend;
  }

  function setBanked($status){
    $this->_banked = (bool)$status;
  }

  function isBanked(){
    return $this->_banked;
  }


  function loadFriends(){
    if (!isset($this->id))
      return NULL;

    $db = & new PHPWS_DB("related_friends");
    $db->addWhere("source_id", $this->id);
    $db->addOrder("rating");
    $db->addColumn("friend_id");
    $result = $db->select("col");

    if (PEAR::isError($result) || empty($result))
      return $result;

    foreach ($result as $id)
      $this->friends[] = & new Related($id);

  }

  function isSame($object){
    if ($this->getMainId() == $object->getMainId() &&
	$this->getModule() == $object->getModule() &&
	$this->getItemName(TRUE) == $object->getItemName(TRUE)
	)
      return TRUE;
    else
      return FALSE;
  }

  function isFriend($checkObj){
    if (empty($this->friends))
      return FALSE;

    foreach ($this->friends as $friend){
      if($friend->isSame($checkObj))
	return TRUE;
    }

    return FALSE;
  }

  function hasFriends(){
    return !empty($this->friends);
  }

  function moveFriendUp($position){
    if (empty($this->friends))
      return FALSE;

    $friends = $this->friends;
    $this->friends = array();
    $currentFriend = $friends[$position];

    if ($position == 0){
      unset($friends[0]);
      $friends[] = $currentFriend;
    } else {
      $replace = $friends[$position - 1];
      $friends[$position - 1] = $currentFriend;
      $friends[$position] = $replace;
    }

    ksort($friends);

    foreach ($friends as $friend)
      $this->friends[] = $friend;
  }

  function moveFriendDown($position){
    if (empty($this->friends))
      return FALSE;

    $friends = $this->friends;
    $this->friends = array();
    $currentFriend = $friends[$position];

    $lastkey = count($friends) - 1;

    if ($position == $lastkey){
      unset($friends[$lastkey]);
      $friends[-1] = $currentFriend;
    } else {
      $replace = $friends[$position + 1];
      $friends[$position + 1] = $currentFriend;
      $friends[$position] = $replace;
    }

    ksort($friends);

    foreach ($friends as $friend)
      $this->friends[] = $friend;
  }

  function removeFriend($position){
    if (empty($this->friends))
      return FALSE;

    $friends = $this->friends;
    $this->friends = array();
    
    $friend = $friends[$position];
    
    if (isset($friend->id)){
      $friend->kill();
    }

    unset($friends[$position]);

    foreach ($friends as $friend)
      $this->friends[] = $friend;
  }


  function load(){
    if (!isset($this->id)){
      $db = & new PHPWS_DB("related_main");
      $db->addWhere("module", $this->getModule());
      $db->addWhere("main_id", $this->getMainId());
      $db->addWhere("item_name", $this->getItemName(TRUE));
      $result = $db->loadObject($this);
      if (PEAR::isError($result))
	return $result;

      $this->loadFriends();
    }
  }

  function show($allowEdit=TRUE){
    PHPWS_Core::initCoreClass("Module.php");
    Layout::addStyle("related");

    $this->load();
    if (!Current_User::allow("related") || (bool)$allowEdit == FALSE)
      $mode = "view";
    elseif (Related_Action::isBanked())
      $mode = "edit";
    elseif (isset($this->id))
      $mode = "view";
    else
      $mode = "create";

    $content['TITLE'] = RELATED_TITLE;

    switch ($mode){
    case "create":
      $body = Related_Action::create($this);
      break;

    case "edit":
      $body = Related_Action::edit($this);
      break;

    case "view":
      $body = Related_Action::view($this);
      break;
    }

    if (!empty($body)) {
      $content['CONTENT'] = &$body;
      Layout::add($content, "related", "bank");
    }

    return TRUE;
  }

  function save(){
    $db = & new PHPWS_DB("related_main");

    if (isset($this->id))
      $db->addWhere("id", $this->id);

    if (!isset($this->item_name))
      $this->item_name = $this->module;

    $result = $db->saveObject($this);

    if (PEAR::isError($result))
      return $result;

    if (!is_array($this->friends))
      return;

    $count = 0;
    $this->clearRelated();
    foreach ($this->friends as $rating=>$friend){
      $friend->save();
      $this->friends[$rating] = $friend;
      $this->addRelation($friend->id, $rating);
    }

    foreach ($this->friends as $rating=>$friend){
      $subfriends = $this->friends;
      $subfriends[$rating] = $this;

      $friend->clearRelated();
      foreach ($subfriends as $subrating=>$subfriend){
	$friend->addRelation($subfriend->id, $subrating);
      }
    }
  }

  function clearRelated(){
    $db = & new PHPWS_DB("related_friends");
    $db->addWhere("source_id", $this->id);
    $result = $db->delete();
  }

  function clearFriends(){
    $db = & new PHPWS_DB("related_friends");
    $db->addWhere("friend_id", $this->id);
    $result = $db->delete();
  }

  function kill(){
    $this->clearRelated();
    $this->clearFriends();
    $db = & new PHPWS_DB("related_main");
    $db->addWhere("id", $this->id);
    $db->delete();
  }

  function addRelation($id, $rating){
    $db = & new PHPWS_DB("related_friends");
    $db->addValue("source_id", $this->id);
    $db->addValue("friend_id", $id);
    $db->addValue("rating", $rating);
    $db->insert();
  }

}

?>