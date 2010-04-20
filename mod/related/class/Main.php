<?php


/**
 * Object for storing related items
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('related', 'Friend.php');

class Related {

    public $id        = NULL;
    public $main_id   = NULL;
    public $module    = NULL;
    public $item_name = NULL;
    public $title     = NULL;
    public $url       = NULL;
    public $rating    = NULL;
    public $active    = FALSE;
    public $friends   = NULL;

    public function setId($id){
        $this->id = (int)$id;
    }

    public function getId(){
        return $this->id;
    }

    public function setMainId($main_id){
        $this->main_id = $main_id;
    }

    public function getMainId(){
        return $this->main_id;
    }

    public function setModule($module){
        $this->module = $module;
    }

    public function getModule(){
        return $this->module;
    }

    public function setItemName($item_name){
        $this->item_name = $item_name;
    }

    public function getItemName(){
        return $this->item_name;
    }


    public function setTitle($title){
        $this->title = preg_replace('/[^\w\s]/', '', strip_tags($title));
    }

    public function getTitle(){
        return $this->title;
    }

    public function setUrl($url){
        $this->url = $url;
    }

    public function getUrl(){
        return $this->url;
    }

    public function setRating($rating){
        $this->rating = (int)$rating;
    }

    public function getRating(){
        return $this->rating;
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

    public function getFriends(){
        return $this->friends();
    }

    public function addFriend($friend){
        $this->friends[$friend->getRating()] = $friend;
    }


    public function loadFriends(){
        if (!isset($this->id))
        return NULL;

        $db = new PHPWS_DB('related_friends');
        $db->addWhere('source_id', $this->id);
        $db->addOrder('rating');
        $db->setIndexBy('rating');
        $result = $db->select('col');

        if (PHPWS_Error::isError($result))
        return $result;

        $this->friends = $result;
    }


}

?>