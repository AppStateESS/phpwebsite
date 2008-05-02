<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Staff {
    var $user_id       = 0;
    var $filter        = null;
    var $available     = 0;
    var $visitor_id    = 0;
    var $_display_name = null;
    var $_reasons      = null;

    function Checkin_Staff($id=0)
    {
        if (empty($id)) {
            return true;
        }

        $this->user_id = (int)$id;
        if (!$this->init()) {
            $this->user_id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('checkin_staff');
        $db->addWhere('user_id', $this->user_id);
        $db->addJoin('left', 'checkin_staff', 'users', 'user_id', 'id');
        $db->addColumn('users.display_name', null, '_display_name');
        return $db->loadObject($this);
    }

    function loadReasons()
    {

    }

    function getFilter()
    {
        return $this->filter;
    }
}

?>