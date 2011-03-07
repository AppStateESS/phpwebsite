<?php

/**
 * Analytics Tracker Abstract Class
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Tracker
{
    var $id;
    var $name;
    var $type;
    var $active;
    var $account;

    public function __construct()
    {
        $this->type = $this->trackerType();
    }

    public abstract function track();
    public abstract function trackerType();

    public function delete()
    {
        $db = new PHPWS_DB('analytics_tracker');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)) {
            return $result;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('analytics_tracker');
        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            return $result;
        }
    }

    public static function addEndBody($content)
    {
        Layout::add($content, 'analytics', 'end_body');
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function isActive()
    {
        return $this->active != 0;
    }

    public function setActive()
    {
        $this->active = 1;
    }

    public function setInactive()
    {
        $this->active = 0;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }
}

?>
