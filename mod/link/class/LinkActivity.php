<?php

/**
 * Link Activity
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class LinkActivity {

    var $id;
    var $link_id;
    var $ip;
    var $user_id;
    var $action;

    public __construct($id = NULL)
    {
        if(is_null($id)) return;

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('link_activity');
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

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

}

?>
