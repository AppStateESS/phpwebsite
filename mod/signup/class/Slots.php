<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Signup_Slot {

    var $id         = 0;
    var $sheet_id   = 0;
    var $title      = null;
    var $openings   = 0;
    var $s_order    = 1;

    var $_peeps     = null;
    var $_filled    = 0;

    function Signup_Slot($id=0)
    {
        if ($id) {
            $this->id = (int)$id;
            $this->init();
        }
    }

    function init()
    {
        $db = new PHPWS_DB('signup_slots');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result) || !$result) {
            $this->id = 0;
            return false;
        }
        return true;
    }

    function loadPeeps($registered=true)
    {
        PHPWS_Core::initModClass('signup', 'Peeps.php');

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

    function setOpenings($openings)
    {
        $this->openings = (int)$openings;
    }

    function setSheetId($sheet_id)
    {
        if (!is_numeric($sheet_id)) {
            return false;
        } else {
            $this->sheet_id = (int)$sheet_id;
            return true;
        }
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function save()
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

    function applicantAddLink()
    {
        $vars['aop']      = 'add_slot_peep';
        $jsadd['label']   = dgettext('signup', 'Add applicant');
        $jsadd['address'] = PHPWS_Text::linkAddress('signup', $vars, true);
        $jsadd['width'] = 300;
        $jsadd['height'] = 470;
        return javascript('open_window', $jsadd);
    }

    function slotLinks()
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


    function showPeeps()
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

        if ($this->_peeps) {
            $jsconf['QUESTION'] = dgettext('signup', 'Are you sure you want to delete this person from their signup slot?');
            $jsconf['LINK'] = sprintf('<img src="images/mod/signup/delete.png" title="%s" />', dgettext('signup', 'Delete'));
            $jspop['label'] = sprintf('<img src="images/mod/signup/edit.png" title="%s" />', dgettext('signup', 'Edit'));

            foreach ($this->_peeps as $peep) {
                $links = array();
                $subtpl['FIRST_NAME'] = $peep->first_name;
                $subtpl['LAST_NAME'] = $peep->last_name;
                $subtpl['EMAIL'] = $peep->getEmail();
                $subtpl['PHONE'] = $peep->getPhone();
                $subtpl['ORGANIZATION'] = $peep->organization;

                $vars['peep_id'] = $peep->id;
                $vars['aop']     = 'edit_slot_peep';
                $jspop['address'] = PHPWS_Text::linkAddress('signup', $vars, true);

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


    function viewTpl()
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
        $tpl['CLOSE'] = javascript('close_window');
        return $tpl;
    }

    function listTpl()
    {
        $vars['address'] = PHPWS_Text::linkAddress('signup', array('aop'=>'edit_peep_popup',
                                                                   'slot_id'=>$this->id));
        $vars['label'] = $this->title;
        $vars['width'] = 800;
        $vars['height'] = 600;
                                        
        $tpl['TITLE'] = javascript('open_window', $vars);
        $tpl['OPENINGS'] = sprintf(dgettext('signup', 'Total openings: %s'), $this->openings);
        $left = $this->openings - $this->_filled;
        $tpl['LEFT'] = sprintf(dgettext('signup', 'Slots left: %s'), $left);
        $tpl['LINKS'] = $this->slotLinks();
        return $tpl;
    }

    function moveUp()
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

    function moveDown()
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

    function delete()
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

    function currentOpenings()
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