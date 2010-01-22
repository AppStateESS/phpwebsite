<?php

/**
 * Link
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Link {

    var $id;
    var $key_id;
    var $href;
    var $title;
    var $other;
    var $placement = 0;
    var $rating = 2.5;

    public function __construct($id = NULL)
    {
        if(is_null($id)) return;

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('link');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('link');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }

        return $this->id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getKeyId()
    {
        return $this->key_id;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = $key_id;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function setHref($href)
    {
        $this->href = $href;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getOther()
    {
        return $this->other;
    }

    public function setOther($other)
    {
        $this->other = $other;
    }

    public function getPlacement()
    {
        return $this->placement;
    }

    public function setPlacement($placement)
    {
        $this->placement = $placement;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

}

?>
