<?php

namespace Properties;

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
class Message {

    public $id;
    public $to_user_id;
    public $from_user_id;
    public $body;
    public $date_sent;
    public $reported = 0;
    public $sender_name;
    public $hidden = 0;

    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }
        $this->id = (int) $id;
        $db = new \PHPWS_DB('prop_messages');
        $db->loadObject($this);
    }

    public function setSenderName($sender)
    {
        $this->sender_name = substr($sender, 0, 1) . '....' . substr($sender, -1);
    }

    public function setToUser($id)
    {
        $this->to_user_id = (int) $id;
    }

    public function setFromUser($id)
    {
        $this->from_user_id = (int) $id;
    }

    public function setBody($body)
    {
        $this->body = strip_tags($body);
    }

    public function getBody()
    {
        return nl2br($this->body);
    }

    public function setHidden($hidden)
    {
        $this->hidden = (int)$hidden;
    }

    public function save()
    {
        if (!$this->to_user_id || !$this->from_user_id) {
            return \PHPWS_Error('Cannot save message.');
        }
        $this->date_sent = time();
        $db = new \PHPWS_DB('prop_messages');
        return $db->saveObject($this);
    }

    public function getOptions()
    {
        if (!$this->reported) {
            $opt[] = sprintf('<a style="cursor : pointer" class="message" id="%s">%s</a>', $this->from_user_id, \Icon::show('undo', 'Reply to contact'));
            $opt[] = sprintf('<a style="cursor:pointer" class="report" id="%s">%s</a>', $this->id, \Icon::show('warning', 'Report'));
            $opt[] = javascript('confirm', array('question' => 'Are you sure you want to delete this message?',
                'address' => \PHPWS_Text::linkAddress('properties', array('rop' => 'delete_message', 'id' => $this->id)),
                'link' => \Icon::show('delete'), 'title' => 'Delete message'));
            return implode('', $opt);
        } else {
            return null;
        }
    }

    public function getDate()
    {
        return date('g:ma M j, Y', $this->date_sent);
    }

    public function getRow()
    {
        static $flip = 0;
        $row = array('DATE' => $this->getDate(),
            'ACTION' => $this->getOptions(),
            'MESSAGE' => $this->getBody(),
            'NAME' => $this->sender_name);
        if ($this->reported) {
            $row['TOGGLE'] = ' class="reported"';
        } elseif ($flip) {
            $row['TOGGLE'] = ' class="toggle"';
        }
        $flip = $flip ? 0 : 1;
        return $row;
    }

    public function delete()
    {
        $db = new \PHPWS_DB('prop_messages');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

}

?>
