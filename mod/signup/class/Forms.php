<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Signup_Forms {
    var $signup = null;
    function get($type)
    {
        switch ($type) {
        case 'new':
        case 'edit_sheet':
            if (empty($this->signup->sheet)) {
                $this->signup->loadSheet();
            }
            $this->editSheet();
            break;

        case 'list':
            $this->signup->panel->setCurrentTab('list');
            $this->listSignup();
            break;

        case 'edit_slots':
            $this->editSlots();
            break;

        case 'edit_peep':
            $this->editPeep();
            break;

        case 'edit_slot_popup':
            $this->editSlotPopup();
            break;

        case 'user_signup':
            $this->userSignup();
            break;

        }

    }

    function editPeep()
    {
        $peep = & $this->signup->peep;

        $form = new PHPWS_Form;
        $form->addHidden('module', 'signup');
        $form->addHidden('aop', 'post_peep');

        if ($peep->id) {
            $form->addSubmit(dgettext('signup', 'Update'));
            $form->addHidden('peep_id', $peep->id);
            $this->signup->title = dgettext('signup', 'Update applicant');
        } else {
            $form->addSubmit(dgettext('signup', 'Add'));
            $this->signup->title = dgettext('signup', 'Add applicant');
        }

        $form->addHidden('sheet_id', $this->signup->sheet->id);
        $form->addHidden('slot_id', $this->signup->slot->id);

        $form->addText('first_name', $peep->first_name);
        $form->setLabel('first_name', dgettext('signup', 'First name'));

        $form->addText('last_name', $peep->last_name);
        $form->setLabel('last_name', dgettext('signup', 'Last name'));

        $form->addText('email', $peep->email);
        $form->setLabel('email', dgettext('signup', 'Email address'));

        $form->addText('phone', $peep->getPhone());
        $form->setLabel('phone', dgettext('signup', 'Phone number'));
        
        $tpl = $form->getTemplate();

        $tpl['CLOSE'] = javascript('close_window');
            
        $this->signup->content = PHPWS_Template::process($tpl, 'signup', 'edit_peep.tpl');
    }

    function editSlotPopup()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'signup');
        $form->addHidden('aop', 'post_slot');
        $form->addHidden('sheet_id', $this->signup->sheet->id);
        if ($this->signup->slot->id) {
            $this->signup->title = sprintf(dgettext('signup', 'Update %s slot'), $this->signup->sheet->title);
            $form->addHidden('slot_id', $this->signup->slot->id);
            $form->addSubmit(dgettext('signup', 'Update'));
        } else {
            $this->signup->title = sprintf(dgettext('signup', 'Add slot to %s'), $this->signup->sheet->title);
            $form->addSubmit(dgettext('signup', 'Add'));
        }

        $form->addText('title', $this->signup->slot->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('signup', 'Title'));

        $form->addText('openings', $this->signup->slot->openings);
        $form->setSize('openings', 5);
        $form->setLabel('openings', dgettext('signup', 'Number of openings'));

        $tpl = $form->getTemplate();

        $tpl['CLEAR'] = javascript('close_window');

        $this->signup->content = PHPWS_Template::process($tpl, 'signup', 'edit_slot.tpl');
    }

    function editSlots()
    {
        $this->signup->title = sprintf(dgettext('signup', 'Slot setup for %s'), $this->signup->sheet->title);

        $vars['aop'] = 'edit_slot_popup';
        $vars['sheet_id'] = $this->signup->sheet->id;
        $vars['slot_id'] = 0;
        $js['address'] = PHPWS_Text::linkAddress('signup', $vars, true);
        $js['label'] = dgettext('signup', 'Add slot');
        $tpl['ADD_SLOT'] = javascript('open_window', $js);

        $slots = $this->signup->sheet->getAllSlots();

        if (PHPWS_Error::logIfError($slots)) {
            $this->signup->content = dgettext('signup', 'An error occurred when accessing this sheet\'s slots.');
            return;
        }

        if ($slots) {
            foreach ($slots as $slot) {
                $tpl['current-slots'][] = $slot->viewTpl();
            }
        }

        $this->signup->content = PHPWS_Template::process($tpl, 'signup', 'slot_setup.tpl');
    }

    function editSheet()
    {
        $form = new PHPWS_Form('signup_sheet');
        $sheet = & $this->signup->sheet;

        $form->addHidden('module', 'signup');
        $form->addHidden('aop', 'post_sheet');
        if ($sheet->id) {
            $form->addHidden('id', $sheet->id);
            $form->addSubmit(dgettext('signup', 'Update'));
            $this->signup->title = dgettext('signup', 'Update signup sheet');
            $form->addTplTag('EDIT_SLOT', $this->signup->sheet->editSlotLink());
        } else {
            $form->addSubmit(dgettext('signup', 'Create'));
            $this->signup->title = dgettext('signup', 'Create signup sheet');
        }

        $form->addText('title', $sheet->title);
        $form->setLabel('title', dgettext('signup', 'Title'));

        $form->addTextArea('description', $sheet->description);
        $form->setLabel('description', dgettext('signup', 'Description'));

        $form->addText('start_time', $sheet->getStartTime());
        $form->setLabel('start_time', dgettext('signup', 'Start signup'));
        $js_vars['date_name'] = 'start_time';
        $js_vars['type'] = 'text';
        $form->addTplTag('ST_JS', javascript('js_calendar', $js_vars));

        $js_vars['date_name'] = 'end_time';
        $form->addText('end_time', $sheet->getEndTime());
        $form->setLabel('end_time', dgettext('signup', 'Close signup'));
        $form->addTplTag('ET_JS', javascript('js_calendar', $js_vars));

        $tpl = $form->getTemplate();

        $this->signup->content = PHPWS_Template::process($tpl, 'signup', 'edit_sheet.tpl');
    }

    function listSignup()
    {
        $ptags['TITLE_HEADER'] = dgettext('signup', 'Title');

        PHPWS_Core::initModClass('signup', 'Sheet.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('signup_sheet', 'Signup_Sheet');
        $pager->setModule('signup');
        $pager->setTemplate('sheet_list.tpl');
        $pager->addRowTags('rowTag');
        $pager->addPageTags($ptags);

        $this->signup->content = $pager->get();
        $this->signup->title = dgettext('signup', 'Signup Sheets');
    }

    function userSignup()
    {
        $sheet = & $this->signup->sheet;
        $peep  = & $this->signup->peep;

        $slots = $sheet->getAllSlots();
        $slots_filled = $sheet->totalSlotsFilled();

        foreach ($slots as $slot) {
            // if the slots are filled, don't offer it
            if ( $slots_filled &&
                 $slots_filled[$slot->id]) {
                $filled = & $slots_filled[$slot->id];
                if ($filled >= $slot->openings) {
                    continue;
                } else {
                    $openings_left = $slot->openings - $filled;
                }
            } else {
                $openings_left = & $slot->openings;
            }

            $options[$slot->id] = sprintf('%s (%s openings)', $slot->title, $openings_left);
        }

        if (!isset($options)) {
            $tpl['MESSAGE'] = dgettext('signup', 'Sorry, but all available slots are full. Please check back later for possible cancellations.');
        } else {
            $form = new PHPWS_Form('slots');
            $form->useFieldset();
            $form->setLegend(dgettext('signup', 'Signup form'));
            $form->addHidden('module', 'signup');
            $form->addHidden('uop', 'slot_signup');
            $form->addHidden('sheet_id', $this->signup->sheet->id);

            $form->addSelect('slot_id', $options);
            $form->setLabel('slot_id', dgettext('signup', 'Available slots'));

            $form->addText('first_name', $peep->first_name);
            $form->setLabel('first_name', dgettext('signup', 'First name'));

            $form->addText('last_name', $peep->last_name);
            $form->setLabel('last_name', dgettext('signup', 'Last name'));

            $form->addText('email', $peep->email);
            $form->setLabel('email', dgettext('signup', 'Email address'));

            $form->addText('phone', $peep->getPhone());
            $form->setLabel('phone', dgettext('signup', 'Phone number'));

            $form->addSubmit(dgettext('signup', 'Submit'));
            
            $tpl = $form->getTemplate();
        }


        $this->signup->title = & $sheet->title;
        $tpl['MESSAGE'] = $this->signup->message;

        $tpl['DESCRIPTION'] = $sheet->getDescription();
        $this->signup->content = PHPWS_Template::process($tpl, 'signup', 'signup_form.tpl');
    }

}

?>