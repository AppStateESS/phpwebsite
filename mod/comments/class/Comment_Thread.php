<?php

/**
 * Class for comment threads. Threads hold all the comments for
 * a specific item.
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */ 

PHPWS_Core::initModClass('comments', 'Comment_Item.php');

define('NO_COMMENTS_FOUND', 'none');

class Comment_Thread {
  var $id          = 0;
  var $module      = NULL;
  var $item_name   = NULL;
  var $item_id     = NULL;
  var $source_url = NULL;
  var $_key        = NULL;
  var $_comments   = NULL;
  var $_error      = NULL;


  function Comment_Thread($id=NULL)
  {
    if (empty($id)) {
      return;
    }

    $this->setId($id);
    $this->init();
  }

  function init()
  {
    $db = & new PHPWS_DB('comments_threads');
    $db->addWhere('id', $this->id);
    $result = $db->loadObject($this);
    if (PHPWS_Error::log($result)) {
      PHPWS_Error::log($result);
      $this->_error = $result->getMessage();
    }
  }

  function countComments($formatted=FALSE)
  {
    $db = & new PHPWS_DB('comments_items');
    $db->addWhere('thread_id', $this->id);
    $count =  $db->count();
    if ($formatted) {
      if (empty($count)) {
	return _('No comments');
      } elseif ($count == 1) {
	return _('1 comment');
      } else {
	return sprintf(_('%s comments'), $count);
      }
    } else {
      return $count;
    }
  }

  function getLastPoster()
  {
    $db = & new PHPWS_DB('comments_items');
    $db->addWhere('thread_id', $this->id);
    $db->setLimit(1);
    $db->addOrder('create_time DESC');
    $db->addColumn('author_name');
    $result = $db->select('one');
    return $result;
  }

  /**
   * Creates a new thread
   *
   * If there is a thread in the database, it is loaded.
   * If there is NOT then one is created.
   */
  function buildThread()
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
      $result = $this->save();
      if (PEAR::isError($result)) {
	PHPWS_Error::log($result);
	$this->_error = _('Error occurred trying to create new thread.');
      }
      return TRUE;
    } else {
      return TRUE;
    }
  }


  function setId($id)
  {
    $this->id = (int)$id;
  }

  function getId()
  {
    return $this->id;
  }

  function setSourceUrl($link)
  {
    $this->source_url = $link;
  }

  function getSourceUrl($full=FALSE)
  {
    if ($full==TRUE) {
      return '<a href="' . $this->source_url . '">' . _('Go Back') . '</a>';
    } else {
      return htmlentities($this->source_url, ENT_QUOTES);
    }
  }

  function setKey($key)
  {
    $this->_key = $key;
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


  function postLink()
  {
    $vars['user_action']   = 'post_comment';
    $vars['thread_id']     = $this->id;
    return PHPWS_Text::moduleLink(_('Post Comment'), 'comments', $vars);
  }

  function save()
  {
    $db = & new PHPWS_DB('comments_threads');
    return $db->saveObject($this);
  }

  function flatView()
  {
    PHPWS_Core::initCoreClass('DBPager.php');

    $page_tags['NEW_POST_LINK'] = $this->postLink();
    $pager = & new DBPager('comments_items', 'Comment_Item');
    $pager->setModule('comments');
    $pager->setTemplate('flat_view.tpl');
    $pager->setLink($this->source_url);
    $pager->addPageTags($page_tags);
    $pager->addRowTags('getTpl');
    $pager->addWhere('thread_id', $this->id);
    $pager->setLimitList(array(10, 20, 50));
    $pager->setEmptyMessage(_('No comments'));
    $content = $pager->get();
    return $content;
  }

  function getAll()
  {

    switch (CURRENT_VIEW_MODE) {
    case THREADED_VIEW:
      $content = $this->threadedView();
      break;
	
    case NESTED_VIEW:
      $content = $this->nestedView();
      break;
	
    case FLAT_VIEW:
      $content = $this->flatView();
      break;
    }

    return $content;
  }

  function getItemTemplates($comments)
  {
    foreach ($comments as $cm_item) {
      $tpl = $cm_item->getTpl();
      $tpl['REPLY_LINK'] = $this->replyLink($cm_item);
      if ( ( ($cm_item->getAuthorId() > 0) 
	     && (Current_User::getId() == $cm_item->getAuthorId()) )
	   || Current_User::allow('comments')) {
	$tpl['EDIT_LINK'] = $this->editLink($cm_item);
      }
      $comment_list[] = $tpl;
    }

    return $comment_list;
  }

}

?>