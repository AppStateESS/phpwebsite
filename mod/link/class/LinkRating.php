<?php

/**
 * Link Rating
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class LinkRating {

    var $id;
    var $link_id;
    var $ip;
    var $rating;

    public __construct($id = NULL)
    {
        if(is_null($id)) return;

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('link_rating');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }
    }

    public function save()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLinkId()
    {
        return $this->link_id;
    }

    public function setLinkId($link_id)
    {
        $this->link_id = $link_id;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
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
