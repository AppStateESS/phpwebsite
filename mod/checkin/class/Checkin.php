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
    var $visitor = null;
    var $reason  = null;

    function loadStaff($id=0, $load_reasons=false)
    {
        PHPWS_Core::initModClass('checkin', 'Staff.php');

        if (!$id && !empty($_REQUEST['staff_id'])) {
            $id = (int)$_REQUEST['staff_id'];
        }
        if ($id) {
            $this->staff = new Checkin_Staff($id);
            if ($load_reasons) {
                $this->staff->loadReasons();
            }
        } else {
            $this->staff = new Checkin_Staff;
        }
    }

    function loadReason($id=0)
    {
        PHPWS_Core::initModClass('checkin', 'Reasons.php');

        if (!$id && !empty($_REQUEST['reason_id'])) {
            $id = (int)$_REQUEST['reason_id'];
        }

        if ($id) {
            $this->reason = new Checkin_Reasons($id);
        } else {
            $this->reason = new Checkin_Reasons;
        }
    }

    function getReasons($all=false)
    {
        $db = new PHPWS_DB('checkin_reasons');
        if (!$all) {
            $db->addColumn('id');
            $db->addColumn('summary');
            $db->setIndexBy('id');
        }
        return $db->select();
    }

    function loadVisitor($id=0)
    {
        PHPWS_Core::initModClass('checkin', 'Visitors.php');
        
        if (!$id && !empty($_REQUEST['visitor_id'])) {
            $this->visitor = new Checkin_Visitor;
        } else {
            $this->visitor = new Checkin_Visitor((int)$_REQUEST['visitor_id']);
        }
    }

}


?>