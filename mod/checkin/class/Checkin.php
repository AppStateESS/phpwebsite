<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin {
    var $title   = null;
    var $message = null;
    var $content = null;
    var $staff   = null;

    function loadStaff()
    {
        $id = 0;
        PHPWS_Core::initModClass('checkin', 'Staff.php');
        if (!empty($_REQUEST['staff_id'])) {
            $id = & $_REQUEST['staff_id'];
        } 
        $this->staff = new Checkin_Staff($id);
    }

    function getReasons()
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addColumn('id');
        $db->addColumn('summary');
        $db->setIndexBy('id');
        return $db->select('col');
    }

}


?>