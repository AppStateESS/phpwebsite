<?php

/**
 * Main control class for Related
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


PHPWS_Core::configRequireOnce('related', 'config.php');
PHPWS_Core::initModClass('related', 'Action.php');

class Related {

    public $id        = NULL;
    public $key_id    = NULL;
    public $title     = NULL;
    public $friends   = NULL;
    public $_banked   = FALSE;
    public $_current  = NULL;
    public $_key      = NULL;


    public function __construct($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->setId($id);
        $result = $this->init();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('related_main');
        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!$result) {
            $this->id = NULL;
        } else {
            $this->_key = new Key($this->key_id);
        }

    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setKey($key)
    {
        if (Key::isKey($key)) {
            $this->_key = $key;
            $this->key_id = $key->id;
        } elseif (is_numeric($key)) {
            $this->key_id = $key;
            $this->_key = new Key($this->key_id);
        }

        if (empty($this->title)) {
            $this->title = $this->_key->title;
        }

    }

    public function setTitle($title){
        $this->title = preg_replace('/[^' . ALLOWED_TITLE_CHARS . ']/', '', strip_tags($title));
    }

    public function getUrl($clickable=FALSE)
    {
        if ($clickable) {
            return sprintf('<a href="%s">%s</a>', PHPWS_Text::fixAmpersand($this->_key->url), $this->title);
        }
        else {
            return $this->_key->url;
        }
    }


    public function setActive($active){
        $this->active = (bool)$active;
    }

    public function isActive(){
        return $this->active;
    }

    public function setFriends($friends){
        $this->friends = $friends;
    }

    public function addFriend($friend){
        $this->friends[] = $friend;
    }

    public function setBanked($status){
        $this->_banked = (bool)$status;
    }

    public function isBanked(){
        return $this->_banked;
    }


    public function loadFriends(){
        if (!isset($this->id)) {
            return NULL;
        }

        $db = new PHPWS_DB('related_friends');
        $db->addWhere('source_id', $this->id);
        $db->addOrder('rating');
        $db->addColumn('friend_id');
        $result = $db->select('col');

        if (PEAR::isError($result) || empty($result)) {
            return $result;
        }

        foreach ($result as $id) {
            $this->friends[] = new Related($id);
        }

    }


    public function isFriend($checkObj){
        if (empty($this->friends)) {
            return FALSE;
        }

        foreach ($this->friends as $friend) {
            if($friend->key_id == $checkObj->key_id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function listFriends()
    {
        if (empty($this->friends)) {
            return NULL;
        }

        foreach ($this->friends as $friend) {
            if ($friend->_key->allowView()) {
                $list[] = $friend->getURL(TRUE);
            }
        }

        if (empty($list)) {
            return NULL;
        }
        return $list;
    }


    public function moveFriendUp($position){
        if (empty($this->friends))
            return FALSE;

        $friends = $this->friends;
        $this->friends = array();
        $currentFriend = $friends[$position];

        if ($position == 0){
            unset($friends[0]);
            $friends[] = $currentFriend;
        } else {
            $replace = $friends[$position - 1];
            $friends[$position - 1] = $currentFriend;
            $friends[$position] = $replace;
        }

        ksort($friends);

        foreach ($friends as $friend)
            $this->friends[] = $friend;
    }

    public function moveFriendDown($position){
        if (empty($this->friends))
            return FALSE;

        $friends = $this->friends;
        $this->friends = array();
        $currentFriend = $friends[$position];

        $lastkey = count($friends) - 1;

        if ($position == $lastkey){
            unset($friends[$lastkey]);
            $friends[-1] = $currentFriend;
        } else {
            $replace = $friends[$position + 1];
            $friends[$position + 1] = $currentFriend;
            $friends[$position] = $replace;
        }

        ksort($friends);

        foreach ($friends as $friend)
            $this->friends[] = $friend;
    }

    public function removeFriend($position){
        if (empty($this->friends))
            return FALSE;

        $friends = $this->friends;
        $this->friends = array();

        $friend = $friends[$position];

        if (isset($friend->id)){
            $friend->kill();
        }

        unset($friends[$position]);

        foreach ($friends as $friend)
            $this->friends[] = $friend;
    }


    public function load(){
        if (!isset($this->id)) {
            $db = new PHPWS_DB('related_main');
            $db->addWhere('key_id', $this->key_id);
            $result = $db->loadObject($this);
            if (PEAR::isError($result)) {
                return $result;
            }

            $this->loadFriends();
        }
    }

    public function show($allowEdit=TRUE)
    {
        PHPWS_Core::initCoreClass('Module.php');

        $key = Key::getCurrent();

        if (empty($key) || $key->isDummy() || empty($key->title) || empty($key->url)) {
            return NULL;
        }

        $related = new Related;
        $related->setKey($key);
        $related->load();

        if (!Current_User::allow('related') || (bool)$allowEdit == FALSE) {
            $mode = 'view';
        }
        elseif (Related_Action::isBanked()) {
            $mode = 'edit';
        }
        elseif (isset($related->id)) {
            $mode = 'view';
        }
        else {
            $mode = 'create';
        }

        switch ($mode){
        case 'create':
            $body = Related_Action::create($related);
            break;

        case 'edit':
            $body = Related_Action::edit($related);
            break;

        case 'view':
            $body = Related_Action::view($related);
            break;
        }

        if (!empty($body)) {
            $content = &$body;

            Layout::add($content, 'related', 'bank');
        }

        return TRUE;
    }

    public function save()
    {
        $db = new PHPWS_DB('related_main');
        $result = $db->saveObject($this);

        if (PEAR::isError($result))
            return $result;

        if (!is_array($this->friends))
            return;

        $count = 0;
        $this->clearRelated();
        foreach ($this->friends as $rating=>$friend){
            $friend->save();
            $this->friends[$rating] = $friend;
            $this->addRelation($friend->id, $rating);
        }

        foreach ($this->friends as $rating=>$friend){
            $subfriends = $this->friends;
            $subfriends[$rating] = $this;

            $friend->clearRelated();
            foreach ($subfriends as $subrating=>$subfriend){
                $friend->addRelation($subfriend->id, $subrating);
            }
        }
    }

    public function clearRelated(){
        $db = new PHPWS_DB('related_friends');
        $db->addWhere('source_id', $this->id);
        $result = $db->delete();
    }

    public function clearFriends(){
        $db = new PHPWS_DB('related_friends');
        $db->addWhere('friend_id', $this->id);
        $result = $db->delete();
    }

    public function kill(){
        $this->clearRelated();
        $this->clearFriends();
        $db = new PHPWS_DB('related_main');
        $db->addWhere('id', $this->id);
        $db->delete();
    }

    public function addRelation($id, $rating){
        $db = new PHPWS_DB('related_friends');
        $db->addValue('source_id', $this->id);
        $db->addValue('friend_id', $id);
        $db->addValue('rating', $rating);
        $db->insert();
    }

}

?>