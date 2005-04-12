<?php

PHPWS_Core::initModClass('comments', 'Comment_Item.php');

class Comment_Thread {
  var $id        = 0;
  var $module    = NULL;
  var $item_name = NULL;
  var $item_id   = NULL;
  var $_comments = NULL;
  var $_error    = NULL;

  function Comment_Thread($key=NULL)
  {
    if (empty($key)) {
      return;
    }

    $this->setKey($key);
    $this->init();
  }

  function init()
  {
    $db = & new PHPWS_DB('comments_threads');
    $db->addWhere('module', $this->module);
    $db->addWhere('item_name', $this->item_name);
    $db->addWhere('item_id', $this->item_id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result)) {
      $this->_error($result->getMessage());
      return $result;
    } elseif (empty($result)) {
      $this->_error = _('Warning: comment thread not found');
      return FALSE;
    } else {
      return TRUE;
    }
  }

  function countComments()
  {
    $db = & new PHPWS_DB('comments_items');
    $db->addWhere('thread_id', $this->id);
    return $db->count();
  }
  

  function setKey($key)
  {
    $this->setModule($key->getModule());
    $this->setItemName($key->getItemName());
    $this->setItemId($key->getItemId());
  }

  function setModule($module)
  {
    $this->module = $module;
  }

  function setItemName($item_name)
  {
    $this->item_name = $item_name;
  }

  function setItemId($item_id)
  {
    $this->item_id = (int)$item_id;
  }

  function getComments()
  {
    $this->loadComments();
    if (empty($this->_comments)) {
      return NULL;
    }
    return $this->_comments;
  }

  function loadComments()
  {
    $db = & new PHPWS_DB('comments_items');
    $db->addWhere('thread_id', $this->id);
    $db->addOrder('parent');
    $db->addOrder('create_time');
    $result = $db->getObjects('Comment_Item');
    if (PEAR::isError($result)) {
      PHPWS_Error::log($result);
      $this->_error = $result;
      return NULL;
    }
    $this->_comments = $result;
  }

  function save()
  {
    $db = & new PHPWS_DB('comments_threads');
    return $db->saveObject($this);
  }

}

?>