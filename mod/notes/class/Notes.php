<?php
PHPWS_Core::initModClass('notes', 'Note_Item.php');

class Notes {

  function add($title, $content, $user_id=NULL)
  {
    $note = & new Note_Item();
    $note->setTitle($title);
    $note->setContent($content);
    if (isset($user_id)) {
      $note->setUserId($user_id);
    }
    $result = $note->save();
    if (PEAR::isError($result)) {
      
    }
  }

  function showNotes()
  {
    if (!Current_User::isLogged() ||
	isset($_SESSION['No_Notes'])) {
      return;
    }

    $db = & new PHPWS_DB('notes');
    $db->addWhere('user_id', Current_User::getId());
    $db->addOrder('title');
    $result = $db->getObjects('Note_Item');

    /*
    if (empty($result)) {
      $_SESSION['No_Notes'] = 1;
    }
    */
    foreach ($result as $note) {
      $template['note'][] = array('NOTE_TITLE' => $note->getTitle());
    }
    $content['TITLE'] = _("Notes");
    $content['CONTENT'] = PHPWS_Template::process($template, 'notes', 'small_list.tpl');
    Layout::add($content, 'notes', 'reminder', TRUE);
  }

}

?>