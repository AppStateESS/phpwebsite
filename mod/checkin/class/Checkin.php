<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin {
    var $title         = null;
    var $message       = null;
    var $content       = null;
    var $staff         = null;
    var $visitor       = null;
    var $reason        = null;
    var $status        = null;
    var $visitor_list  = null;
    var $staff_list    = null;
    var $current_staff = 0;

    /**
     * staff_id = 0
     * would load the unassigned visitors
     */
    function loadVisitorList($staff_id=null, $index=false)
    {
        PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $db = new PHPWS_DB('checkin_visitor');
        if ($index) {
            $db->setIndexBy('assigned');
        }
        if (isset($staff_id)) {
            $db->addWhere('assigned', $staff_id);
        }
        $db->addOrder('arrival_time desc');
        $result = $db->getObjects('Checkin_Visitor');
        //        test($result);
        if (!PHPWS_Error::logIfError($result)) {
            $this->visitor_list = & $result;
        }
    }


    function loadStaffList()
    {
        PHPWS_Core::initModClass('checkin', 'Staff.php');
        $db = new PHPWS_DB('checkin_staff');
        $db->addColumn('users.display_name');
        $db->addColumn('checkin_staff.*');
        $db->addWhere('user_id', 'users.id');
        $db->addOrder('users.display_name');
        $result = $db->getObjects('Checkin_Staff');
        if (!PHPWS_Error::logIfError($result)) {
            $this->staff_list = & $result;
        }
     }

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

    function loadStatus($id=0)
    {
        PHPWS_Core::initModClass('checkin', 'Status.php');

        if (!$id && !empty($_REQUEST['status_id'])) {
            $id = (int)$_REQUEST['status_id'];
        }
        if ($id) {
            $this->status = new Checkin_Status($id);
        } else {
            $this->status = new Checkin_Status;
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
        $db->addOrder('summary');
        if (!$all) {
            $db->addColumn('id');
            $db->addColumn('summary');
            $db->setIndexBy('id');
            return $db->select('col');
        }
        return $db->select();
    }

    function loadVisitor($id=0)
    {
        PHPWS_Core::initModClass('checkin', 'Visitors.php');
        
        if (!$id || !empty($_REQUEST['visitor_id'])) {
            $this->visitor = new Checkin_Visitor;
        } else {
            $this->visitor = new Checkin_Visitor((int)$_REQUEST['visitor_id']);
        }
    }

    function getStaffList($as_object=false)
    {
        $db = new PHPWS_DB('checkin_staff');
        $db->addWhere('user_id', 'users.id');
        $db->addColumn('users.display_name');
        if ($as_object) {
            PHPWS_Core::initModClass('checkin', 'Staff.php');
            $db->addColumn('*');
            return $db->getObjects('Checkin_Staff');
        } else {
            $db->addColumn('id');
            $db->setIndexBy('id');
            return $db->select('col');
        }
    }

    function getStatusList()
    {
        static $status_list = null;

        if (!is_array($status_list)) {
            $db = new PHPWS_DB('checkin_status');
            $db->setIndexBy('id');
            $status_list = $db->select();
        }
        if (is_array($status_list)) {
            $status_list = array_reverse($status_list, true);
            $status_list[0] = array('id'=>0, 'available'=>0,
                                    'color'=>'#7df774',
                                    'summary'=>dgettext('checkin', 'Available for meeting'));
            $status_list = array_reverse($status_list, true);
        }
        return $status_list;
    }

    function timeWaiting($timestamp)
    {
        $rel   = time() - $timestamp;

        $hours = floor( $rel / 3600);
        if ($hours) {
            $rel = $rel % 3600;
        }

        $mins = floor( $rel / 60);

        if ($hours) {
            $waiting[] = sprintf(dngettext('checkin', '%s hour', '%s hours', $hours), $hours);
        }

        if ($mins) {
            $waiting[] = sprintf(dngettext('checkin', '%s minute', '%s minutes', $mins), $mins);
        }

        if (!isset($waiting)) {
            $waiting[] = dgettext('checkin', 'Just arrived');
        }

        return implode(', ', $waiting);

    }
}
?>