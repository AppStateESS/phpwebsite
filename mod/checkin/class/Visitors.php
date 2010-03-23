<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Visitor {
    public $id            = 0;
    public $firstname     = null;
    public $lastname      = null;
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


    public function assign()
    {
        if (!$this->reason) {
            return;
        }

        $db = new PHPWS_DB('checkin_rtos');
        $db->addWhere('reason_id', $this->reason);
        $db->addWhere('staff_id', 'checkin_staff.id', null, null, 'staff');
        $db->addWhere('checkin_staff.status', 1, '!=', null, 'staff');
        $db->addColumn('staff_id');
        // currently only grabbing one staff member
        $this->assigned = $db->select('one');

        if (!$this->assigned) {
            $db = new PHPWS_DB('checkin_staff');
            $db->addWhere('f_regexp', null, '!=');
            $db->addColumn('id');
            $db->addColumn('f_regexp');
            $db->setIndexBy('id');

            $filters = $db->select('col');
            if (empty($filters)) {
                $this->assigned = 0;
                return;
            }

            foreach ($filters as $id=>$preg_filter) {
                if (empty($preg_filter)) {
                    continue;
                }

                // preg match requires parens and first character symbol
                if (preg_match("/^($preg_filter)/i", $this->lastname)) {
                    $this->assigned = $id;
                    return;
                }
            }

            $this->assigned = 0;
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
