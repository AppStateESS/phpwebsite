<?php

class Note_Item {
  var $id        = NULL;
  var $user_id   = NULL;
  var $check_key = NULL;
  var $title     = NULL;
  var $content   = NULL;

  function Note_Item($id = NULL)
  {
    if (empty($id)) {
      return;
    }

    $this->id = (int)$id;
    $this->init();
  }

  function init()
  {
    if (empty($this->id)) {
      return FALSE;
    }
    $db = & new PHPWS_DB('notes');
    return $db->loadObject($this);
  }

  function setUserId($user_id)
  {
    $this->user_id = (int)$user_id;
  }

  function setTitle($title)
  {
    $this->title = strip_tags(trim($title));
  }

  function getTitle()
  {
    return $this->title;
  }

  function setContent($content)
  {
    $this->content = trim($content);
  }

  function isDuplicate()
  {
    $db = & new PHPWS_DB('notes');
    $db->addWhere('check_key', $this->check_key);
    $check_result = $db->select('one');
    if (PEAR::isError($check_result)) {
      PHPWS_Error::log($check_result);
      return TRUE;
    } else {
      return (bool)$check_result;
    }
  }

  function save()
  {
    if (!isset($this->user_id)) {
      $this->user_id = Current_User::getId();
    }

    $this->check_key = md5($this->user_id . $this->title . $this->content);
    if ($this->isDuplicate()) {
      return FALSE;
    } else {
      $db = & new PHPWS_DB('notes');
      return $db->saveObject($this);
    }
  }

}

?>