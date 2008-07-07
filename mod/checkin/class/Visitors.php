<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Visitor {
    var $id            = 0;
    var $firstname     = null;
    var $lastname      = null;
    var $reason        = 0;
    var $arrival_time  = 0;
    var $start_meeting = 0;
    var $end_meeting   = 0;
    var $assigned      = false;
    var $note          = null;
    var $finished      = false;

    function Checkin_Visitor($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $db = new PHPWS_DB('checkin_visitor');
        if (!$db->loadObject($this)) {
            $this->id = 0;
        } 
    }

    function save()
    {
        $db = new PHPWS_DB('checkin_visitor');
        if (empty($this->arrival_time)) {
            $this->arrival_time = mktime();
        }
        return $db->saveObject($this);
    }

    function assign()
    {
        if (!$this->reason) {
            return;
        }

        $db = new PHPWS_DB('checkin_rtos');
        $db->addWhere('reason_id', $this->reason);
        $db->addColumn('staff_id');
        // currently only grabbing one staff member
        $this->assigned = $db->select('one');

        if (!$this->assigned) {
            $db = new PHPWS_DB('checkin_staff');
            $db->addColumn('id');
            $db->addColumn('filter');
            $db->setIndexBy('id');
            $filters = $db->select('col');
            if (empty($filters)) {
                return;
            }
            foreach ($filters as $id=>$filter) {
                $lastname = preg_quote($this->lastname);
                $filter = str_replace(' ', '', $filter);
                $farray = explode(',', $filter);

                foreach ($farray as $val) {
                    switch (1) {
                    case preg_match('/-/', $val):
                            
                        break;
                    }
                }

            }
        }
    }
}
