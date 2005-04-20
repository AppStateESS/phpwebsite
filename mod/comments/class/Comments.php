<?php

/**
 * Developer class for accessing comments
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('THREADED_VIEW', 1);
define('NESTED_VIEW',   2);
define('FLAT_VIEW',     3);

// This will be set by config and cookie later
define('CURRENT_VIEW_MODE', 3);

PHPWS_Core::initModClass('comments', 'Comment_Thread.php');

class Comments {

  function &makeThread($key, $source_url)
  {
    $thread = & new Comment_Thread;
    $thread->setKey($key);
    $thread->setSourceUrl($source_url);
    $thread->buildThread();
    return $thread;
  }

  function userAction($command)
  {
    if (isset($_REQUEST['thread_id'])) {
      $thread = & new Comment_Thread($_REQUEST['thread_id']);
    } else {
      $thread = & new Comment_Thread;
    }
    
    switch ($command) {
    case 'post_comment':
      $title = _('Post Comment');
      $content[] = Comments::form($thread);
      break;

    case 'save_comment':
      if (!isset($thread)) {
	$title = _('Error');
	$content[] = _('Missing thread information.');
	break;
      }

      $result = Comments::saveComment($thread);
      if (PEAR::isError($result)) {
	PHPWS_Error::log($result);
	$title = _('Sorry');
	$content[] = _('A problem occurred when trying to save your comment.');
	$content[] = _('Please try again later.');
      } else {
	Layout::metaRoute($thread->source_url);
	$title = _('Comment saved successfully!');
	$content[] = _('You will be returned to the source page in a moment.');
	$content[] = '<a href="' . $thread->source_url . '">' . 
	  _('Otherwise you may return immediately by clicking here.') .
	  '</a>';
      }
      break;
    }

    $template['TITLE'] = $title;
    $template['CONTENT'] = implode('<br />', $content);

    Layout::add(PHPWS_Template::process($template, 'comments', 'main.tpl'));

  }
  
  function saveComment(&$thread)
  {
    if (isset($_POST['cm_id'])) {
      $cm_item = & new Comment_Item($_POST['cm_id']);
    } else {
      $cm_item = & new Comment_Item;
    }

    $cm_item->setThreadId($thread->id);
    $cm_item->setSubject($_POST['cm_subject']);
    $cm_item->setEntry($_POST['cm_entry']);
    if (isset($_POST['cm_parent'])) {
      $cm_item->setParent($_POST['cm_parent']);
    }

    if ($cm_item->id) {
      if (isset($_POST['edit_reason'])) {
	$cm_item->setEditReason($_POST['edit_reason']);
      }
    }

    return $cm_item->save();
  }

  function form(&$thread)
  {
    if (isset($_REQUEST['cm_id'])) {
      $c_item = & new Comment_Item($_REQUEST['cm_id']);
    } else {
      $c_item = & new Comment_Item;
    }

    $form = & new PHPWS_Form;
    
    if (isset($_REQUEST['cm_parent'])) {
      $c_parent = & new Comment_Item($_REQUEST['cm_parent']);
      $form->addHidden('cm_parent', $c_parent->getId());
      $form->addTplTag('PARENT_SUBJECT', $c_parent->getSubject());
      $form->addTplTag('PARENT_ENTRY', $c_parent->getEntry());
    }
    
    if (!empty($c_item->id)) {
      $form->addHidden('cm_id', $c_item->id);
      $form->addText('edit_reason', $c_item->getEditReason());
      $form->setLabel('edit_reason', _('Reason for edit'));
      $form->setSize('edit_reason', 50);
    }

    $form->addHidden('module', 'comments');
    $form->addHidden('user_action', 'save_comment');
    $form->addHidden('thread_id',    $thread->getId());

    $form->addText('cm_subject');
    $form->setLabel('cm_subject', _('Subject'));
    $form->setSize('cm_subject', 50);

    if (isset($c_parent) && empty($c_item->subject)) {
      $form->setValue('cm_subject', _('Re:') . $c_parent->getSubject());
    } else {
      $form->setValue('cm_subject', $c_item->getSubject());
    }
    
    $form->addTextArea('cm_entry', $c_item->getEntry(FALSE));
    $form->setLabel('cm_entry', _('Comment'));
    $form->setCols('cm_entry', 50);
    $form->setRows('cm_entry', 10);

    $form->addSubmit(_('Post Comment'));

    $template = $form->getTemplate();
    $template['BACK_LINK'] = $thread->getSourceUrl(TRUE);

    $content = PHPWS_Template::process($template, 'comments', 'edit.tpl');
    return $content;
  }


  function unregister($module)
  {
    $db = & new PHPWS_DB('comments_threads');
    $db->addWhere('module', $module);
    $db->addColumn('id');
    $id_list = $db->select('col');
    if (empty($id_list)) {
      return TRUE;
    } elseif (PEAR::isError($id_list)) {
      PHPWS_Error::log($id_list);
      return FALSE;
    }

    $db2 = & new PHPWS_DB('comments_items');
    foreach ($id_list as $id) {
      $db2->addWhere('thread_id', $id, NULL, 'OR');
    }
    $result = $db2->delete();
    if (PEAR::isError($result)) {
      PHPWS_Error::log($id_list);
      return FALSE;
    } else {
      return TRUE;
    }
  }

}

?>