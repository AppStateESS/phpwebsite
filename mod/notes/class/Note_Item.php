<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Note_Item {
    public $id        = NULL;
    public $user_id   = NULL;

    /**
     * ID of user who sent message
     * If zero, then it is a system note
     */
    public $sender_id = 0;

    public $title     = NULL;
    public $content   = NULL;
    /**
     * Indicates the note has been read
     */
    public $read_once = 0;

    /**
     * Indicates the note data is encrypted
     */
    public $encrypted = 0;

    /**
     * Timestamp of creation
     */
    public $date_sent = 0;


    /**
     * Name of sender. Not saved.
     */
    public $sender = null;

    /**
     * Name of user to receive note. Not saved.
     */
    public $username = null;

    /**
     * Associates a note to keyed item
     */
    public $key_id   = 0;

    public function __construct($id = NULL, $confirm_user=true)
    {
        if (empty($id)) {
            return;
        }
        $this->id = (int)$id;
        $this->init($confirm_user);
    }


    public function delete($confirm=true)
    {
        $db = new \core\DB('notes');
        $db->addWhere('id', $this->id);
        if ($confirm) {
            $db->addWhere('user_id', Current_User::getId());
        }
        return $db->delete();
    }

    /**
     * Translated from call
     */
    public function deleteLink()
    {
        $vars = Notes_My_Page::myPageVars(false);
        $vars['op'] = 'delete_note';
        $vars['id'] = $this->id;

        return \core\Text::secureLink(dgettext('notes', 'Delete'), 'users', $vars);
    }

    public function getContent()
    {
        return \core\Text::parseOutput($this->content, ENCODE_PARSED_TEXT, true);
    }

    public function getDateSent($format=null)
    {
        if (empty($format)) {
            $format = '%c';
        }
        return strftime($format, $this->date_sent);
    }


    public function getTags()
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


    public function init($confirm_user=true)
    {
        if (empty($this->id)) {
            return FALSE;
        }
        $db = new \core\DB('notes');
        $db->addWhere('id', $this->id);
        if ($confirm_user) {
            $db->addWhere('user_id', Current_User::getId());
        }
        $db->addWhere('sender_id', 'users.id');
        $db->addColumn('users.username', null, 'sender');
        $db->addColumn('*');

        return $db->loadObject($this);
    }


    public function read()
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
            $key = new \core\Key($this->key_id);
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

        return \core\Template::process($tpl, 'notes', 'note.tpl');
    }


    public function readLink($use_title=true)
    {
        $vars = Notes_My_Page::myPageVars();
        $vars['op'] = 'read_note';
        $vars['id'] = $this->id;

        if (javascriptEnabled()) {
            $js_vars['address'] = \core\Text::linkAddress('users', $vars);
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
                return \core\Text::moduleLink($this->title, 'users', $vars, null, dgettext('notes', 'Read note'));
            } else {
                return \core\Text::moduleLink(dgettext('notes', 'Read'), 'users', $vars, null, dgettext('notes', 'Read note'));
            }
        }

    }

    public function save()
    {
        if (empty($this->user_id)) {
            return false;
        }

        $this->date_sent = time();

        $db = new \core\DB('notes');
        return $db->saveObject($this);
    }


    public static function sendLink($user_id=0, $label=null, $popup=true)
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
            $js_vars['address'] = \core\Text::linkAddress('users', $vars);
            $js_vars['label'] = $label;
            $js_vars['link_title'] = $title;
            $js_vars['width'] = 650;
            $js_vars['height'] = 600;
            return javascript('open_window', $js_vars);
        } else {
            return \core\Text::moduleLink($label, 'users', $vars, null, $title);
        }
    }


    public function setUserId($user_id)
    {
        $this->user_id = (int)$user_id;
    }

    public function setTitle($title)
    {
        $this->title = strip_tags(trim($title));
    }


    public function setContent($content)
    {
        $this->content =strip_tags(trim($content));
    }

    public function updateRead()
    {
        unset($_SESSION['Notes_Unread']);
        $db = new \core\DB('notes');
        $db->addWhere('id', $this->id);
        $db->addValue('read_once', 1);
        return $db->update();
    }

}

?>