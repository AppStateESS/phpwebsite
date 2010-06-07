<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Signup_Slot {

    public $id         = 0;
    public $sheet_id   = 0;
    public $title      = null;
    public $openings   = 0;
    public $s_order    = 1;

    public $_peeps     = null;
    public $_filled    = 0;

    public function __construct($id=0)
    {
        if ($id) {
            $this->id = (int)$id;
            $this->init();
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('signup_slots');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result) || !$result) {
            $this->id = 0;
            return false;
        }
        return true;
    }

    public function loadPeeps($registered=true)
    {
        Core\Core::initModClass('signup', 'Peeps.php');

        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('slot_id', $this->id);
        if ($registered) {
            $db->addWhere('registered', 1);
        } else {
            $db->addWhere('registered', 0);
        }

        $db->addOrder('last_name');
        $peeps = $db->getObjects('Signup_Peep');

        if (PHPWS_Error::logIfError($peeps)) {
            return false;
        } else {
            $this->_peeps = & $peeps;
            return true;
        }
    }

    public function setOpenings($openings)
    {
        $this->openings = (int)$openings;
    }

    public function setSheetId($sheet_id)
    {
        if (!is_numeric($sheet_id)) {
            return false;
        } else {
            $this->sheet_id = (int)$sheet_id;
            return true;
        }
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function save()
    {
        if (!$this->sheet_id) {
            return PHPWS_Error::get(SU_NO_SHEET_ID, 'signup', 'Signup_Slot::save');
        }

        $db = new PHPWS_DB('signup_slots');
        if (!$this->id) {
            $db->addWhere('sheet_id', $this->sheet_id);
            $db->addColumn('s_order', 'max');
            $max = $db->select('one');
            if (PHPWS_Error::isError($max)) {
                return $max;
            }
            if ($max >= 1) {
                $this->s_order = $max + 1;
            } else {
                $this->s_order = 1;
            }
            $db->reset();
        }
        return $db->saveObject($this);
    }

    public function applicantAddLink()
    {
        $vars['aop']      = 'add_slot_peep';
        $vars['slot_id']  = $this->id;
        $jsadd['label']   = dgettext('signup', 'Add applicant');
        $jsadd['address'] = PHPWS_Text::linkAddress('signup', $vars, true);
        $jsadd['width']   = 300;
        $jsadd['height']  = 470;
        return javascript('open_window', $jsadd);
    }

    public function slotLinks()
    {
        $vars['slot_id'] = $this->id;

        $vars['aop'] = 'edit_slot_popup';
        $links[] = javascript('open_window', array('label'  => dgettext('signup', 'Edit Slot'),
                                                   'address'=> PHPWS_Text::linkAddress('signup', $vars, true)));

        if ($this->_filled < $this->openings) {
            $links[] = $this->applicantAddLink();
        }

        if (empty($this->_peeps)) {
            $vars['aop'] = 'delete_slot';
            $jsconf['QUESTION'] = dgettext('signup', 'Are you certain you want to delete this slot?');
            $jsconf['ADDRESS'] = PHPWS_Text::linkAddress('signup', $vars, true);
            $jsconf['LINK'] = dgettext('signup', 'Delete slot');
            $links[] = javascript('confirm', $jsconf);
        }


        $vars['aop'] = 'move_up';
        $links[] = PHPWS_Text::secureLink(dgettext('signup', 'Up'), 'signup', $vars);

        $vars['aop'] = 'move_down';
        $links[] = PHPWS_Text::secureLink(dgettext('signup', 'Down'), 'signup', $vars);

        return implode(' | ', $links);
    }


    public function showPeeps()
    {
        $sheet = new Signup_Sheet($this->sheet_id);
        $total_slots = $sheet->totalSlotsFilled();
        $all_slots = $sheet->getAllSlots();
        foreach ($all_slots as $slot) {
            if ($slot->id == $this->id) {
                continue;
            } elseif (!isset($total_slots[$slot->id]) ||
            $slot->openings != $total_slots[$slot->id]) {
                $options[$slot->id] = $slot->title;
            }
        }

        $ex1 = sprintf('<abbr title="%s"><strong>1:</strong></abbr> ', $sheet->extra1);
        $ex2 = sprintf('<abbr title="%s"><strong>2:</strong></abbr> ', $sheet->extra2);
        $ex3 = sprintf('<abbr title="%s"><strong>3:</strong></abbr> ', $sheet->extra3);

        if ($this->_peeps) {
            $jsconf['QUESTION'] = dgettext('signup', 'Are you sure you want to delete this person from their signup slot?');
            $jsconf['LINK'] = Icon::show('delete');
            $jspop['label'] = Icon::show('edit');

            foreach ($this->_peeps as $peep) {
                $links = array();
                $subtpl = array();
                $subtpl['FIRST_NAME'] = $peep->first_name;
                $subtpl['LAST_NAME']  = $peep->last_name;
                $subtpl['EMAIL']      = $peep->getEmail();
                $subtpl['PHONE']      = $peep->getPhone();
                if (!empty($sheet->extra1)) {
                    $subtpl['EXTRA1']     = $ex1 . $peep->getExtra1();
                }

                if (!empty($sheet->extra2)) {
                    $subtpl['EXTRA2']     = $ex2 . $peep->getExtra2();
                }

                if (!empty($sheet->extra3)) {
                    $subtpl['EXTRA3']     = $ex3 . $peep->getExtra3();
                }

                $vars['peep_id'] = $peep->id;
                $vars['aop']     = 'edit_slot_peep';
                $jspop['address'] = PHPWS_Text::linkAddress('signup', $vars, true);
                $jspop['width']  = 300;
                $jspop['height'] = 600;
                $links[] = javascript('open_window', $jspop);

                $vars['aop']     = 'delete_slot_peep';
                $jsconf['ADDRESS'] = PHPWS_Text::linkAddress('signup', $vars, true);
                $links[] = javascript('confirm', $jsconf);

                $subtpl['ACTION'] = implode('', $links);

                if (!empty($options)) {
                    $form = new PHPWS_Form;
                    $form->addHidden('module', 'signup');
                    $form->addHidden('aop', 'move_peep');
                    $form->addHidden('peep_id', $peep->id);
                    $form->addSelect('mv_slot', $options);
                    $form->addSubmit(dgettext('signup', 'Go'));
                    $tmptpl = $form->getTemplate();
                    $subtpl['MOVE'] = implode("\n", $tmptpl);
                } else {
                    $subtpl['MOVE'] = dgettext('signup', 'Other slots full');
                }

                $tpl['peep-row'][] = $subtpl;
            }

            $tpl['NAME_LABEL']         = dgettext('signup', 'Name');
            $tpl['EMAIL_LABEL']        = dgettext('signup', 'Email');
            $tpl['PHONE_LABEL']        = dgettext('signup', 'Phone');
            $tpl['ACTION_LABEL']       = dgettext('signup', 'Action');
            $tpl['ORGANIZATION_LABEL'] = dgettext('signup', 'Organization');
            $tpl['MOVE_LABEL']         = dgettext('signup', 'Move');

            return PHPWS_Template::process($tpl, 'signup', 'peeps.tpl');
        }
    }


    public function viewTpl()
    {
        $tpl['TITLE'] = $this->title;
        $tpl['OPENINGS'] = sprintf(dgettext('signup', 'Total openings: %s'), $this->openings);
        $this->loadPeeps();

        $left = $this->openings - count($this->_peeps);
        $tpl['LEFT'] = sprintf(dgettext('signup', 'Slots left: %s'), $left);

        $tpl['PEEPS'] = $this->showPeeps();
        $filled = count($this->_peeps);
        if ($filled< $this->openings) {
            $tpl['ADD'] = $this->applicantAddLink();
        }

        javascript('close_refresh', array('use_link'=>1));
        $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="closeWindow(); return false" />',
        dgettext('signup', 'Close'));
        return $tpl;
    }

    public function listTpl()
    {
        $vars['address'] = PHPWS_Text::linkAddress('signup', array('aop'=>'edit_peep_popup',
                                                                   'slot_id'=>$this->id));
        $vars['label']      = $this->title;
        $vars['width']      = 800;
        $vars['height']     = 600;
        $vars['link_title'] = dgettext('signup', 'Click to view and edit signups for this slot.');

        $tpl['TITLE'] = javascript('open_window', $vars);
        $tpl['OPENINGS'] = sprintf(dgettext('signup', 'Total openings: %s'), $this->openings);
        $left = $this->openings - $this->_filled;
        $tpl['LEFT'] = sprintf(dgettext('signup', 'Slots left: %s'), $left);
        $tpl['LINKS'] = $this->slotLinks();
        return $tpl;
    }

    public function moveUp()
    {
        $db = new PHPWS_DB('signup_slots');
        $db->addWhere('sheet_id', $this->sheet_id);
        $db->addColumn('id', null, null, true);
        $slot_count = $db->select('one');

        if ($this->s_order == 1) {
            $db->reduceColumn('s_order', 1);
            $this->s_order = $slot_count;
            $this->save();
        } else {
            $db->resetColumns();
            $db->addWhere('s_order', $this->s_order - 1);
            $db->addValue('s_order', $this->s_order);
            $db->update();
            $this->s_order--;
            $this->save();
        }
    }

    public function moveDown()
    {
        $db = new PHPWS_DB('signup_slots');
        $db->addWhere('sheet_id', $this->sheet_id);
        $db->addColumn('id', null, null, true);
        $slot_count = $db->select('one');

        if ($this->s_order == $slot_count) {
            $db->incrementColumn('s_order', 1);
            $this->s_order = 1;
            $this->save();
        } else {
            $db->resetColumns();
            $db->addWhere('s_order', $this->s_order + 1);
            $db->addValue('s_order', $this->s_order);
            $db->update();
            $this->s_order++;
            $this->save();
        }
    }

    public function delete()
    {
        $db = new PHPWS_DB('signup_slots');
        $db->addWhere('id', $this->id);
        if (PHPWS_Error::logIfError($db->delete())) {
            return false;
        }

        $db->reset();
        $db->addWhere('s_order', $this->s_order, '>');
        $db->reduceColumn('s_order', 1);
        return true;
    }

    public function currentOpenings()
    {
        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('slot_id', $this->id);
        $db->addColumn('id', null, null, true);

        $applicants = $db->select('one');

        if (PHPWS_Error::logIfError($applicants)) {
            return 0;
        } else {
            return $this->openings - $applicants;
        }
    }

}
?>