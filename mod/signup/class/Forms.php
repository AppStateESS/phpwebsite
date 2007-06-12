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
        }

    }

    function editSheet()
    {
        $form = new PHPWS_Form('signup_sheet');
        $sheet = & $this->signup->sheet;

        $form->addHidden('module', 'signup');
        $form->addHidden('aop', 'post_sheet');
        if ($sheet->id) {
            $form->addHidden('s_id', $sheet->id);
            $form->addSubmit(dgettext('signup', 'Update'));
            $this->signup->title = dgettext('signup', 'Update signup sheet');
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

}

?>