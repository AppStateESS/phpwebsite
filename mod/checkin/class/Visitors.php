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

    function noteLink()
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

    function row($staff_list=null)
    {
        static $meeting = 0;

        $form = new PHPWS_Form('form' . $this->id);
        $tpl['NAME'] = sprintf('%s %s', $this->firstname, $this->lastname);
        $tpl['WAITING'] = $this->timeWaiting();
        if ($staff_list) {
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
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function timeWaiting($from_now=true)
    {
        if ($from_now) {
            $rel = time() - $this->arrival_time;
        } else {
            $rel = $this->start_meeting - $this->arrival_time;
        }

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

    function getName()
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }

    function getReason()
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

}
