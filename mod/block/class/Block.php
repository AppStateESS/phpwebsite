<?php

class Block {
  var $id      = 0;
  var $title   = NULL;
  var $content = NULL;
  var $module  = NULL;

  function Block($id=NULL)
  {
    if (empty($id)) {
      return;
    }

    $this->setId($id);
    $this->init();
  }

  function setId($id)
  {
    $this->id = (int)$id;
  }

  function getId()
  {
    return $this->id;
  }

  function setTitle($title)
  {
    $this->title = strip_tags($title);
  }

  function getTitle()
  {
    return $this->title;
  }

  function setContent($content)
  {
    $this->content = PHPWS_Text::parseInput($content);
  }

  function getContent()
  {
    return PHPWS_Text::parseOutput($this->content);
  }

  function setModule($module)
  {
    $this->module = $module;
  }

  function getModule()
  {
    return $this->module;
  }

  function init()
  {
    if (empty($this->id)) {
      return FALSE;
    }

    $db = & new PHPWS_DB('block');
    return $db->loadObject($this);
  }

  function save()
  {
    $db = & new PHPWS_DB('block');
    return $db->saveObject($this);
  }
}

?>