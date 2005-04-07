<?php
/**
 * Contains information for an individual comment
 */

PHPWS_Core::configRequireOnce('comments', 'config.php');

class Comment_Item {
  // Id number of comment
  var $id          = 0;

  // Id of thread
  var $thread_id   = 0

  // Id of comment this comment is a child of
  var $parent      = 0;

  // Order rank of item
  var $cm_order    = 1;

  // Title of comment
  var $title       = NULL;

  // Content of comment
  var $entry       = NULL;

  // Author (display name) of comment writer
  var $author      = NULL;

  // Date comment was created
  var $create_time = NULL;

  // Date comment was edited
  var $edit_time   = NULL;

  // Reason comment was edited
  var $edit_reason = NULL;

  // Name of person who edited the comment
  var $edit_author = NULL;

  // Error encountered when processing object
  var $_error = NULL;

  function Comment_Item($id=NULL)
  {
    if (empty($id)) {
      return;
    }

    $result = $this->init();
    if (PEAR::isError($result)) {
      $this->_error = $result;
    }
  }

  function init()
  {
    if (!isset($this->id))
      return FALSE;

    $db = & new PHPWS_DB('comment_items');
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;
  }


  function setId($id)
  {
    $this->id = (int)$id;
  }

  function getId()
  {
    return $this->id;
  }

  function setThreadId($thread_id)
  {
    $this->thread_id = (int)$thread_id;
  }


  function setParent($parent)
  {
    $this->parent = (int)$parent;
  }


  function setTitle($title)
  {
    $this->title = strip_tags(trim($title));
  }

  function getTitle()
  {
    return $this->title;
  }

  function setEntry($entry)
  {
    $this->entry(PHPWS_Text::parseInput($entry));
  }

  function getEntry($format=TRUE)
  {
    if ($format) {
      return PHPWS_Text::parseOutput($this->entry);
    } else {
      return $this->entry;
    }
  }

  function stampAuthor()
  {
    $this->author = Current_User::getDisplayName();
  }

  function getAuthor()
  {
    return $this->author;
  }

  function stampCreateTime()
  {
    $this->create_time = gmktime();
  }

  function getCreateTime($format=TRUE)
  {
    if ($format) {
      return strftime(COMMENT_DATE_FORMAT, $this->create_time);
    } else {
      return $this->create_time;
    }
  }
  
  function stampEditTime()
  {
    $this->edit_time = gmktime();
  }

  function getStampTime($format=TRUE)
  {
    if ($format) {
      return strftime(COMMENT_DATE_FORMAT, $this->edit_time);
    } else {
      return $this->edit_time;
    }
  }

  function stampEditAuthor()
  {
    $this->edit_author = Current_User::getDisplayName();
  }

  function getEditAuthor()
  {
    return $this->edit_author;
  }

  function getError()
  {
    return $this->_error;
  }

}

?>