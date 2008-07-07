<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Status {
    var $id        = 0;
    var $color     = '#ffffff';
    var $summary   = null;

    /**
     * 0 - available
     * 1 - meeting with visitor
     * 2 - busy
     */
    var $available = 2;

    function Checkin_Status($id=0) {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $db = new PHPWS_DB('checkin_status');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result) || empty($result)) {
            $this->id = 0;
        }
    }

    function row_tags()
    {
        $tpl['COLOR']     = sprintf('<div style="width : 100px; height : 2em; background-color : %s">&#160;</div>', $this->color);
        switch ($this->available) {
        case 0:
            $tpl['AVAILABLE'] = dgettext('checkin', 'Available');
            break;

        case 1:
            $tpl['AVAILABLE'] = dgettext('checkin', 'Occupied w/ visitor');
            break;

        case 2:
            $tpl['AVAILABLE'] = dgettext('checkin', 'Unavailable');
            break;
        }

        $tpl['ACTION'] = 'links';
        return $tpl;
    }
    
    function setSummary($summary)
    {
        $this->summary = trim(strip_tags($summary));
    }

    function setColor($color) {
        if (!preg_match('/#[0-9a-f]{6}/', strtolower($color))) {
            return false;
        } else {
            $this->color = $color;
            return true;
        }
    }

    function save()
    {
        $db = new PHPWS_DB('checkin_status');
        return $db->saveObject($this);
    }

}

?>