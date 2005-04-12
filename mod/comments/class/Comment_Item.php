<?php
/**
 * Contains information for an individual comment
 */

PHPWS_Core::configRequireOnce('comments', 'config.php');

class Comment_Item {
  // Id number of comment
  var $id          = 0;

  // Id of thread
  var $thread_id   = 0;

  // Id of comment this comment is a child of
  var $parent      = 0;

  // Subject of comment
  var $subject       = NULL;

  // Content of comment
  var $entry       = NULL;

  // Author (display name) of comment writer
  var $author      = NULL;

  // IP address of poster
  var $author_ip   = NULL;

  // Date comment was created
  var $create_time = 0;

  // Date comment was edited
  var $edit_time   = 0;

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

    $this->setId($id);
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


  function setSubject($subject)
  {
    $this->subject = strip_tags(trim($subject));
  }

  function getSubject($format=TRUE)
  {
    if ($format) {
      return PHPWS_Text::parseOutput($this->subject);
    } else {
      return $this->subject;
    }
  }

  function setEntry($entry)
  {
    $this->entry = PHPWS_Text::parseInput($entry);
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
    if (Current_User::isLogged()) {
      $this->author = Current_User::getDisplayName();
    } else {
      $this->author = DEFAULT_ANONYMOUS_TITLE;
    }
  }

  function getAuthor()
  {
    return $this->author;
  }

  function stampIP()
  {
    $this->author_ip = $_SERVER['REMOTE_ADDR'];
  }

  function getIP()
  {
    return $this->author_ip;
  }

  function stampCreateTime()
  {
    $this->create_time = gmmktime();
  }

  function getCreateTime($format=TRUE)
  {
    if ($format) {
      return gmstrftime(COMMENT_DATE_FORMAT, $this->create_time);
    } else {
      return $this->create_time;
    }
  }
  
  function stampEditTime()
  {
    $this->edit_time = gmmktime();
  }

  function getEditTime($format=TRUE)
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

  function getTpl()
  {
    $template['SUBJECT']       = $this->getSubject(TRUE);
    $template['SUBJECT_LABEL'] = _('Subject');
    $template['ENTRY']         = $this->getEntry(TRUE);
    $template['ENTRY_LABEL']   = _('Comment');
    $template['AUTHOR']        = $this->getAuthor();
    $template['AUTHOR_LABEL']  = _('Author');
    $template['POSTED_BY']     = _('Posted by');
    $template['POSTED_ON']     = _('Posted on');
    $template['CREATE_TIME']   = $this->getCreateTime();
    if (isset($this->edit_author)) {
      $template['EDIT_AUTHOR'] = $this->getEditAuthor();
      $template['EDIT_TIME']   = $this->getEditTime();
    }
    
    if (Current_User::allow('comments')) {
      $template['IP_ADDRESS'] = $this->getIp();
    }
    return $template;
  }

  function save()
  {
    if (empty($this->thread_id) ||
	empty($this->subject)   ||
	empty($this->entry)) {
      return FALSE;
    }

    if (empty($this->create_time)) {
      $this->stampCreateTime();
    }

    if (empty($this->author)) {
      $this->stampAuthor();
      $this->stampIP();
    }

    if ((bool)$this->id) {
      $this->stampEditTime();
      $this->stampEditAuthor();
    }

    $db = & new PHPWS_DB('comments_items');
    return $db->saveObject($this);
  }

}

?>