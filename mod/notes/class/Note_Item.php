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

  /**
   * Associates a note to keyed item
   */
  var $key_id   = 0;

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
      $db = new PHPWS_DB('notes');
      $db->addWhere('id', $this->id);
      if ($confirm) {
          $db->addWhere('user_id', Current_User::getId());
      }
      return $db->delete();
  }

  /**
   * Translated from call
   */ 
  function deleteLink()
  {
      $vars = Notes_My_Page::myPageVars(false);
      $vars['op'] = 'delete_note';
      $vars['id'] = $this->id;
      
      return PHPWS_Text::secureLink(dgettext('notes', 'Delete'), 'users', $vars);
  }

  function getContent()
  {
      return PHPWS_Text::parseOutput($this->content, ENCODE_PARSED_TEXT, true);
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
          $tpl['READ_ONCE'] = dgettext('notes', 'Yes');
      } else {
          $GLOBALS['Note_Unread'] = true;
          $tpl['NOT_READ_CLASS'] = 'note-not-read';
          $tpl['READ_ONCE'] = dgettext('notes', 'No');
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
    $db = new PHPWS_DB('notes');
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
      if ($this->sender_id) {
          $tpl['SENDER'] = $this->sendLink($this->sender_id, $this->sender, false);
      } else {
          $tpl['SENDER'] = dgettext('notes', 'System message');
      }
      $tpl['DATE_SENT']  = $this->getDateSent();
      $tpl['DATE_LABEL'] = dgettext('notes', 'Sent on');
      $tpl['SENT_LABEL'] = dgettext('notes', 'Sent by');

      if ($this->key_id) {
          $key = new Key($this->key_id);
          if ($key->id) {
              $tpl['ASSOCIATE_LABEL'] = dgettext('notes', 'In reference to');

              if (javascriptEnabled()) {
                  $link = sprintf('<a href="#" onclick="closeWindow(); return false">%s</a>', $key->title);
                  javascript('close_refresh', array('use_link'=>true, 'location'=> $key->url));
              } else {
                  $link = $key->getUrl();
              }
              $tpl['ASSOCIATE'] = $link;
          }
      }

      if (!$this->read_once) {
          $this->updateRead();
      }

      if (javascriptEnabled()) {
          $tpl['CLOSE'] = javascript('close_window');
      }

      $link = sprintf('document.location.href=\'index.php?module=notes&command=delete_note&id=%s\'',
                      $this->id);

      $tpl['DELETE'] = sprintf('<input type="button" onclick="%s" value="%s" />', 
                               $link,
                               dgettext('notes', 'Delete and close'));

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
              $js_vars['label'] = dgettext('notes', 'Read');
          }
          $js_vars['width']      = 640;
          $js_vars['height']     = 480;
          $js_vars['link_title'] = dgettext('notes', 'Read note');
          return javascript('open_window', $js_vars);
      } else {
          if ($use_title) {
              return PHPWS_Text::moduleLink($this->title, 'users', $vars, null, dgettext('notes', 'Read note'));
          } else {
              return PHPWS_Text::moduleLink(dgettext('notes', 'Read'), 'users', $vars, null, dgettext('notes', 'Read note'));
          }
      }
      
  }

  function save()
  {
      if (empty($this->user_id)) {
          return false;
      }

      $this->date_sent = mktime();

      $db = new PHPWS_DB('notes');
      return $db->saveObject($this);
  }
  
  
  function sendLink($user_id=0, $label=null, $popup=true)
  {
      $vars = Notes_My_Page::myPageVars(false);
      $vars['op'] = 'send_note';
      if ($user_id) {
          $vars['uid'] = (int)$user_id;
      }

      if (empty($label)) {
          $title = $label = dgettext('notes', 'Send note');
      } else {
          $title = sprintf(dgettext('notes', 'Send note to %s'), $label);
      }
      
      if ($popup) {
          $js_vars['address'] = PHPWS_Text::linkAddress('users', $vars);
          $js_vars['label'] = $label;
          $js_vars['link_title'] = $title;
          $js_vars['width'] = 640;
          $js_vars['height'] = 480;
            return javascript('open_window', $js_vars);
      } else {
          return PHPWS_Text::moduleLink($label, 'users', $vars, null, $title);
      }
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
      unset($_SESSION['Notes_Unread']);
      $db = new PHPWS_DB('notes');
      $db->addWhere('id', $this->id);
      $db->addValue('read_once', 1);
      return $db->update();
  }

}

?>