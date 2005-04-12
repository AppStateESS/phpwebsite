<?php

/**
 * Developer class for accessing comments
 */

define('THREADED_VIEW', 1);
define('NESTED_VIEW',   2);
define('FLAT_VIEW',     3);

// This will be set by config and cookie later
define('CURRENT_VIEW_MODE', 3);

PHPWS_Core::initModClass('comments', 'Comment_Thread.php');

class Comments {

  function countComments($key)
  {
    $c_thread = & new Comment_Thread($key);
    if (empty($c_thread->id)) {
      return 0;
    }

    return $c_thread->countComments();
  }

  function &getForm()
  {
    if (isset($_REQUEST['comment_id'])) {
      $c_item = & new Comment_Item($_REQUEST['comment_id']);
    } else {
      $c_item = & new Comment_Item;
    }

    $form = & new PHPWS_Form;

    if (!empty($c_item->id)) {
      $form->addHidden('cm_id', $c_item->id);
    }

    $form->addText('cm_subject', $c_item->getSubject());
    $form->setLabel('cm_subject', _('Subject'));
    $form->setSize('cm_subject', 40);

    $form->addTextArea('cm_entry', $c_item->getEntry());
    $form->setLabel('cm_entry', _('Comment'));
    $form->setCols('cm_entry', 50);
    $form->setRows('cm_entry', 10);

    return $form;
  }

  function getAll($key)
  {
    $c_thread = & new Comment_Thread($key);
    $result = $c_thread->getComments();

    if (empty($result)) {
      return _('No comments.');
    }

    switch (CURRENT_VIEW_MODE) {
    case THREADED_VIEW:
      $content = Comments::viewThreaded($result);
      break;

    case NESTED_VIEW:
      $content = Comments::viewNested($result);
      break;

    case FLAT_VIEW:
      $content = Comments::viewFlat($result);
      break;
      
    }
    return $content;
  }

  function viewFlat($comments)
  {
    foreach ($comments as $cm_item) {
      $comment_list[] = $cm_item->getTpl();
    }

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

  function post($key)
  {
    $c_thread = & new Comment_Thread($key);
    if (empty($c_thread->id)) {
      $result = $c_thread->save();
      if (PEAR::isError($result)) {
	return $result;
      }
    }

    if (isset($_POST['cm_id'])) {
      $cm_item = & new Comment_Item($_POST['cm_id']);
    } else {
      $cm_item = & new Comment_Item;
    }

    $cm_item->setThreadId($c_thread->id);
    $cm_item->setSubject($_POST['cm_subject']);
    $cm_item->setEntry($_POST['cm_entry']);
    return $cm_item->save();
  }

}

?>