<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Visitor {
    public $id            = 0;
    public $firstname     = null;
    public $lastname      = null;
    public $email         = null;
    public $gender        = null;
    public $birthdate     = null;
    public $reason        = 0;
    public $arrival_time  = 0;
    public $start_meeting = 0;
    public $end_meeting   = 0;
    public $assigned      = 0;
    public $note          = null;
    public $finished      = false;

    public function __construct($id=0)
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

    public function save()
    {
        $db = new PHPWS_DB('checkin_visitor');
        if (empty($this->arrival_time)) {
            $this->arrival_time = time();
        }
        return $db->saveObject($this);
    }

    /**
     * Assigns a visitor to the proper staff member.
     * 
     * If the visitor can not be matched to any staff member, then the visitor
     * is left unassigned. If the visitor can be matched to more than 1 staff 
     * member, then the visitor is matched with the staff member with the 
     * fewest visitors waiting.
     */
    public function assign()
    {
        $availableStaff = array();

        $db = new PHPWS_DB('checkin_staff');
        $db->addWhere('active', 1);         // staff is not deactivated
        $db->addWhere('status', 1, '!=');   // staff is not unavailable
        $db->addOrder('id asc');
        $availableStaff = $db->select();

        foreach ($availableStaff as $key => $staff) {
            // Eliminate staff members who don't meet with people of the visitor's gender
            if ($staff['filter_type'] & GENDER_BITMASK) {
                if ($staff['gender_filter'] != $this->gender) {
                    unset($availableStaff[$key]);
                    continue;
                }
            }
            
            // Eliminate staff members who don't meet with people with the visitor's last name
            if ($staff['filter_type'] & LAST_NAME_BITMASK) {
                $regex = $staff['lname_regexp'];
                if (!preg_match("/^($regex)/i", $this->lastname)) {
                    unset($availableStaff[$key]);
                    continue;
                }
            }
            
            // Eliminate staff members who don't meet with people of the visitor's age
            if ($staff['filter_type'] & BIRTHDATE_BITMASK) {
                if ($this->birthdate < $staff['birthdate_filter_start'] || $this->birthdate > $staff['birthdate_filter_end']) {
                    unset($availableStaff[$key]);
                    continue;
                }
            }
            
            // Eliminate staff members who don't meet with people having the visitor's "reason for visit"
            if ($staff['filter_type'] & REASON_BITMASK) {
                $rtosDB = new PHPWS_DB('checkin_rtos');
                $rtosDB->addWhere('staff_id', $staff['id']);
                $rtosDB->addColumn('reason_id');
                $reasons = $rtosDB->select('col');
                $flag = false;
                foreach ($reasons as $possReason) {
                    if ($this->reason == $possReason) {
                        $flag = true;
                    }
                }
                if (!$flag) {
                    unset($availableStaff[$key]);
                    continue;
                }
            }
        }

        $availableStaff = array_values($availableStaff);    // reindex the array
        
        // Match the visitor to one of the remaining eligible staff members, if any eligible staff members exist.
        if (count($availableStaff) == 0) {          // no eligible staff members
            $this->assigned = 0;
        } elseif (count($availableStaff) == 1) {    // one eligible staff member
            $this->assigned = $availableStaff[0]['id'];
        } else {                                    // more than one eligible staff member
            // If multiple staff members remain, assign the visitor to the one who has the fewest waiting visitors.
            $counts = array();
            $visitorDB = new PHPWS_DB('checkin_visitor');
            foreach ($availableStaff as $staff) {
                $visitorDB->addWhere('assigned', $staff['id']);
                $visitorDB->addWhere('finished', '0');
                $visitorDB->addColumn('id', null, null, true);
                $count = $visitorDB->select();
                $counts[$staff['id']] = $count;
                $visitorDB->reset();
            }
            $id = array_keys($counts, min($counts));
            $this->assigned = is_array($id) ? $id[0] : $id;
        }
    }

    public function removeLink()
    {
        $js['question'] = sprintf(dgettext('checkin', 'Are you sure you want to remove %s from the waiting list?'),
        addslashes($this->getName()));
        $js['address']  = PHPWS_Text::linkAddress('checkin', array('aop'=>'remove_visitor',
                                                                   'visitor_id'=> $this->id));
        $js['link']     = dgettext('checkin', 'Remove');
        $js['title']    = dgettext('checkin', 'Remove visitor from checkin');
        return javascript('confirm', $js);
    }

    public function noteLink()
    {
        static $form_id = 0;
        $form_id++;
        $form = new PHPWS_Form('f_' . $form_id);
        $form->addHidden('module', 'checkin');
        $form->addHidden('aop', 'post_note');
        $form->addHidden('visitor_id', $this->id);
        $tpl = $form->getTemplate();
        $tpl['NOTE'] = $this->note;

        $tpl['NOTE_LINK'] = dgettext('checkin', 'Note');
        $tpl['BUTTON'] = dgettext('checkin', 'Send');
        $tpl['CLOSE'] = dgettext('checkin', 'Close');
        $tpl['TITLE'] = sprintf('Note: %s %s', $this->firstname,$this->lastname);
        return PHPWS_Template::process($tpl, 'checkin', 'note.tpl');
    }

    public function row($staff_list=null, &$staff)
    {
        static $meeting = 0;

        $form = new PHPWS_Form('form' . $this->id);
        $tpl['NAME'] = sprintf('%s %s', $this->firstname, $this->lastname);
        $tpl['ARRIVED'] = strftime(PHPWS_Settings::get('checkin', 'time_format'),$this->arrival_time);
        $tpl['WAITING'] = Checkin::timeWaiting(time() - $this->arrival_time);
        if ($staff_list && $staff->visitor_id != $this->id) {
            $select = sprintf('visitor_%s', $this->id);
            $form->addSelect($select, $staff_list);
            $form->setExtra($select, sprintf('onchange="reassign(this, %s)"', $this->id));
            $tpl['MOVE'] = $form->get($select);
        }

        if ($this->note) {
            $tpl['NOTE'] = $this->note;
        }

        $tpl['REASON'] = $this->getReason();

        $links[] = $this->noteLink();
        if (Current_User::allow('checkin', 'remove_visitors') && $staff->visitor_id != $this->id) {
            $links[] = $this->removeLink();
        }

        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    public function getName()
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }

    public function getReason()
    {
        static $reasons = null;

        if (empty($reasons)) {
            $reasons = Checkin::getReasons();
        }

        if (isset($reasons[$this->reason])) {
            return $reasons[$this->reason];
        } else {
            return dgettext('checkin', 'Reason unknown');
        }
    }

    public function delete()
    {
        $db = new PHPWS_DB('checkin_visitor');
        $db->addWhere('id', $this->id);
        if (!PHPWS_Error::logIfError($db->delete())) {
            $db = new PHPWS_DB('checkin_staff');
            $db->addWhere('visitor_id', $this->id);
            PHPWS_Error::logIfError($db->update('visitor_id', 0));
        }
    }
}
