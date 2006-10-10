<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Note_Item {
  var $id        = NULL;
  var $user_id   = NULL;

  /**
   * ID of user who sent message
   * If zero, then it is a system note
   */
  var $sender_id = 0;

  var $title     = NULL;
  var $content   = NULL;
  /**
   * Indicates the note has been read
   */
  var $read_once = 0;

  /**
   * Indicates the note data is encrypted
   */
  var $encrypted = 0;

  /**
   * Timestamp of creation
   */
  var $date_sent = 0;


  /**
   * Name of sender. Not saved.
   */
  var $sender = null;

  /**
   * Name of user to receive note. Not saved.
   */
  var $username = null;

  function Note_Item($id = NULL, $confirm_user=true)
  {
    if (empty($id)) {
      return;
    }
    $this->id = (int)$id;
    $this->init($confirm_user);
  }


  function delete($confirm=true)
  {
      $db = & new PHPWS_DB('notes');
      $db->addWhere('id', $this->id);
      if ($confirm) {
          $db->addWhere('user_id', Current_User::getId());
      }
      return $db->delete();
  }

  function deleteLink()
  {
      $vars = Notes_My_Page::myPageVars();
      $vars['op'] = 'delete_note';
      $vars['id'] = $this->id;
      return PHPWS_Text::secureLink(_('Delete'), 'users', $vars);
  }

  function getContent()
  {
      return PHPWS_Text::parseOutput($this->content);
  }

  function getDateSent($format=null)
  {
      if (empty($format)) {
          $format = '%c';
      }
      return strftime($format, $this->date_sent);
  }


  function getTags()
  {

      $tpl['DATE_SENT']  = $this->getDateSent();
      $tpl['TITLE'] = $this->readLink();


      if ($this->read_once) {
          $tpl['READ_CLASS'] = 'note-read';
          $tpl['READ_ONCE'] = _('Yes');
      } else {
          $tpl['NOT_READ_CLASS'] = 'note-not-read';
          $tpl['READ_ONCE'] = _('No');
      }

      $links[] = $this->readLink(false);
      $links[] = $this->deleteLink();

      $tpl['LINKS'] = implode(' | ', $links);

      return $tpl;
  }


  function init($confirm_user=true)
  {
    if (empty($this->id)) {
      return FALSE;
    }
    $db = & new PHPWS_DB('notes');
    $db->addWhere('id', $this->id);
    if ($confirm_user) {
        $db->addWhere('user_id', Current_User::getId());
    }
    $db->addWhere('sender_id', 'users.id');
    $db->addColumn('users.username', null, 'sender');
    $db->addColumn('*');

    return $db->loadObject($this);
  }


  function read()
  {
      $tpl['TITLE'] = $this->title;
      $tpl['CONTENT'] = $this->getContent();
      $tpl['SENDER'] = $this->sender;
      $tpl['DATE_SENT']  = $this->getDateSent();
      $tpl['DATE_LABEL'] = _('Sent on');
      $tpl['SENT_LABEL'] = _('Sent by');

      if (!$this->read_once) {
          $this->updateRead();
      }

      if (javascriptEnabled()) {
          $tpl['CLOSE'] = javascript('close_window');
      }

      return PHPWS_Template::process($tpl, 'notes', 'note.tpl');
  }


  function readLink($use_title=true)
  {
      $vars = Notes_My_Page::myPageVars();
      $vars['op'] = 'read_note';
      $vars['id'] = $this->id;

      if (javascriptEnabled()) {
          $js_vars['address'] = PHPWS_Text::linkAddress('users', $vars);
          if ($use_title) {
              $js_vars['label'] = $this->title;
          } else {
              $js_vars['label'] = _('Read');
          }
          $js_vars['width'] = 500;
          $js_vars['height'] = 400;
          $js_vars['link_title'] = _('Read note');
          return javascript('open_window', $js_vars);
      } else {
          if ($use_title) {
              return PHPWS_Text::moduleLink($this->title, 'users', $vars, null, _('Read note'));
          } else {
              return PHPWS_Text::moduleLink(_('Read'), 'users', $vars, null, _('Read note'));
          }
      }
      
  }

  function save()
  {
    $this->date_sent = mktime();
    $this->sender_id = Current_User::getId();
    $db = & new PHPWS_DB('notes');
    return $db->saveObject($this);
  }

  function setUserId($user_id)
  {
    $this->user_id = (int)$user_id;
  }

  function setTitle($title)
  {
    $this->title = strip_tags(trim($title));
  }


  function setContent($content)
  {
      $this->content =strip_tags(trim($content));
  }

  function updateRead()
  {
      $db = & new PHPWS_DB('notes');
      $db->addWhere('id', $this->id);
      $db->addValue('read_once', 1);
      return $db->update();
  }

}

?>