<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Status {
    var $id        = 0;
    var $available = true;
    var $color     = '#ffffff';
    var $summary   = null;

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
        $tpl['COLOR']     = sprintf('<div style="width : 100px; height : 50px; background-color : %s">&#160;</div>', $this->color);
        $tpl['AVAILABLE'] = $this->available ? dgettext('checkin', 'Yes') : dgettext('checkin', 'No');
        return $tpl;
    }
    
}

?>