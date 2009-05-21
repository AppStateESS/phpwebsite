<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin {
    public $title           = null;
    public $message         = null;
    public $content         = null;
    public $staff           = null;
    public $visitor         = null;
    public $reason          = null;
    public $visitor_list    = null;
    public $staff_list      = null;
    public $unassigned_only = false;


    /**
     * staff_id = 0
     * would load the unassigned visitors
     */
    public function loadVisitorList($staff_id=null, $index=false)
    {
        PHPWS_Core::initModClass('checkin', 'Visitors.php');
        $db = new PHPWS_DB('checkin_visitor');
        if ($index) {
            $db->setIndexBy('assigned', true);
        }
        if (isset($staff_id)) {
            $db->addWhere('assigned', $staff_id);
        }
        $db->addWhere('finished', 0);
        $db->addOrder('arrival_time asc');
        $result = $db->getObjects('Checkin_Visitor');

        if (!PHPWS_Error::logIfError($result)) {
            $this->visitor_list = & $result;
        }
    }


    public function loadStaffList()
    {
        PHPWS_Core::initModClass('checkin', 'Staff.php');
        $db = new PHPWS_DB('checkin_staff');
        $db->addColumn('users.display_name');
        $db->addColumn('checkin_staff.*');
        $db->addWhere('user_id', 'users.id');
        $db->addOrder('checkin_staff.view_order');
        $result = $db->getObjects('Checkin_Staff');
        if (!PHPWS_Error::logIfError($result)) {
            $this->staff_list = & $result;
        }
     }

    public function loadStaff($id=0, $load_reasons=false)
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

    public function loadReason($id=0)
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

    public function getReasons($all=false)
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

    public function loadVisitor($id=0)
    {
        PHPWS_Core::initModClass('checkin', 'Visitors.php');

        if (!$id && isset($_REQUEST['visitor_id'])) {
            $id = (int)$_REQUEST['visitor_id'];
        }

        if (!$id) {
            $this->visitor = new Checkin_Visitor;
        } else {
            $this->visitor = new Checkin_Visitor($id);
        }
    }

    public function getStaffList($as_object=false, $available_only=false)
    {
        $db = new PHPWS_DB('checkin_staff');
        if ($available_only) {
            $db->addWhere('status', 1, '!=');
        }
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

    public function getStatusColors()
    {
        $list[0] = '#39f15a';
        $list[1] = '#f9f95b';
        $list[2] = '#f96567';
        $list[3] = '#519FF9';

        return $list;
    }

    public function getStatusList()
    {
        $list[0] = dgettext('checkin', 'Available');
        $list[1] = dgettext('checkin', 'Unavailable');
        $list[2] = dgettext('checkin', 'Meeting with visitor');
        $list[3] = dgettext('checkin', 'Send back');

        return $list;
    }


    public function parseFilter($filter)
    {
        $filter = strtolower(str_replace(' ', '', $filter));
        $farray = explode(',', $filter);

        foreach ($farray as $val) {
            $subval = explode('-', $val);
            switch (1) {
            case strlen($val) == 1:
                $final[] = $val;
                break;

            case preg_match('/^\w{1}-\w{1}$/', $val):
                $final[] = "[$val]";
                break;

            case preg_match('/^\w{2}-\w{2}$/', $val):
                if (substr($subval[0], 0, 1) == substr($subval[1], 0, 1)) {
                    $final[] = sprintf('%s[%s-%s]', substr($subval[0], 0, 1),
                                       substr($subval[0], 1, 1),
                                       substr($subval[1], 1, 1));
                } else {
                    $char1 = substr($subval[0], 0, 1);
                    $char2 = substr($subval[0], 1, 1);
                    if ($char2 == 'a') {
                        $final[] = $char1;
                    } else {
                        $final[] = sprintf('%s[a-%s]', $char1, $char2);
                    }

                    $char3 = substr($subval[1], 0, 1);
                    $char4 = substr($subval[1], 1, 1);

                    if ($char4 == 'a') {
                        $final[] = $subval[1];
                    } else {
                        $final[] = sprintf('%s[a-%s]', $char3, $char4);
                    }
                }
                break;

            case preg_match('/^\w{1}-\w{2}$/', $val):
                $final[] = $subval[0];
                $char1 = substr($subval[1], 0, 1);
                $char2 = substr($subval[1], 1, 1);
                if ($char2 == 'a') {
                    $final[] = $subval[1];
                } else {
                    $final[] = sprintf('%s[a-%s]', $char1, $char2);
                }
                break;

            case preg_match('/^\w{2}-\w{1}$/', $val):
                $char1 = substr($subval[0], 0, 1);
                $char2 = substr($subval[0], 1, 1);
                $char3 = substr($subval[1], 0, 1);
                if ($char2 == 'z') {
                    $final[] = $subval[0];
                } else {
                    $final[] = sprintf('%s[%s-z]', $char1, $char2);
                }

                $start_char = (int)ord($char1);
                $final_char = (int)ord($char3);
                if ($final_char - $start_char == 1) {
                    $final[] = $subval[1];
                } else {
                    for ($i = $start_char; $i < $final_char; $i++);
                    $final[] = sprintf('[%s-%s]', chr($start_char + 1), chr($i));
                }
                break;

            default:
                $final[] = $val;
                break;
            }
        }
        return sprintf('/^%s/i', implode('|', $final));
    }

    public function timeWaiting($rel)
    {
        if ($rel < 0) {
            return dgettext('checkin', 'Time error');
        }

        $hours = floor( $rel / 3600);
        if ($hours) {
            $rel = $rel % 3600;
        }

        $mins = floor( $rel / 60);

        if ($hours) {
            $waiting[] = sprintf(dgettext('checkin', '%s hour'), $hours);
        }

        if ($mins) {
            $waiting[] = sprintf(dgettext('checkin', '%s min.'), $mins);
        }

        if (!isset($waiting)) {
            if (!$rel) {
                $waiting[] = dgettext('checkin', 'No meeting');
            } else {
                $waiting[] = dgettext('checkin', '< 1 min.');
            }
        }

        return implode(', ', $waiting);
    }

}
?>