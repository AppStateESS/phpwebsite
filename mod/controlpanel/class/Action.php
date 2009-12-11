<?php
  /**
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @version $Id$
   */

class CP_Action {
    public function adminAction()
    {
        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = 'admin_menu';
        }

        switch ($command){
        case 'post_tab':
            if (!empty($_POST['title'])) {
                $tab = new PHPWS_Panel_Tab($_POST['tab_id']);
                $tab->setTitle($_POST['title']);
                PHPWS_Error::logIfError($tab->save());
                $content = javascript('close_refresh');
                break;
            }
        case 'edit_tab_title':
            $tab = new PHPWS_Panel_Tab($_REQUEST['tab_id']);

            $content = CP_Action::editTabTitle($tab);
            if (empty($content)) {
                $content = javascript('close_refresh');
            }
            Layout::nakedDisplay($content);
            break;

        case 'post_link':
            if (!empty($_POST['label'])) {
                $link = new PHPWS_Panel_Link($_POST['link_id']);
                $link->setLabel($_POST['label']);
                $link->setDescription($_POST['description']);
                PHPWS_Error::logIfError($link->save());
                unset($_SESSION['CP_All_links']);
                $content = javascript('close_refresh');
                break;
            }
        case 'edit_link':
            $link = new PHPWS_Panel_Link($_REQUEST['link_id']);

            $content = CP_Action::editLink($link);
            if (empty($content)) {
                $content = javascript('close_refresh');
            }
            Layout::nakedDisplay($content);
            break;

        case 'admin_menu':
            $content = CP_Action::adminMenu();
            break;

        case 'tab_up':
            $tab = new PHPWS_Panel_Tab($_REQUEST['tab_id']);
            $tab->moveup();
            PHPWS_ControlPanel::reset();
            $content = CP_Action::adminMenu();
            break;

        case 'tab_down':
            $tab = new PHPWS_Panel_Tab($_REQUEST['tab_id']);
            $tab->movedown();
            PHPWS_ControlPanel::reset();
            $content = CP_Action::adminMenu();
            break;

        case 'link_up':
            $link = new PHPWS_Panel_Link($_REQUEST['link_id']);
            $link->moveup();
            PHPWS_ControlPanel::reset();
            $content = CP_Action::adminMenu();
            break;

        case 'link_down':
            $link = new PHPWS_Panel_Link($_REQUEST['link_id']);
            $link->movedown();
            PHPWS_ControlPanel::reset();
            $content = CP_Action::adminMenu();
            break;
        }

        $template['TITLE'] = dgettext('controlpanel', 'Control Panel Administration');
        $template['CONTENT'] = $content;
        $final = PHPWS_Template::process($template, 'controlpanel', 'main.tpl');

        Layout::add(PHPWS_ControlPanel::display($final));
    }


    public function adminMenu()
    {
        $tabs = PHPWS_ControlPanel::getAllTabs();
        $links = PHPWS_ControlPanel::getAllLinks();

        $tpl = new PHPWS_Template('controlpanel');
        $tpl->setFile('panelList.tpl');

        $tvalues['module'] = $lvalues['module'] = 'controlpanel';
        $tvalues['action'] = $lvalues['action'] = 'admin';

        $up_tab_command = dgettext('controlpanel', 'Move tab order up');
        $down_tab_command = dgettext('controlpanel', 'Move tab order down');
        $up_tab = Icon::show('sort-up', $up_tab_command);
        $down_tab = Icon::show('sort-down', $down_tab_command);

        $up_link_command = dgettext('controlpanel', 'Move link order up');
        $down_link_command = dgettext('controlpanel', 'Move link order down');
        $up_link = Icon::show('sort-up', $up_link_command);
        $down_link = Icon::show('sort-down', $down_link_command);

        if (count($tabs) > 1)
            $move_tabs = TRUE;
        else
            $move_tabs = FALSE;

        foreach ($tabs as $tab_obj){
            $taction = array();
            if (isset($links[$tab_obj->id])){
                if (count($links[$tab_obj->id]) > 1) {
                    $move_links = TRUE;
                } else {
                    $move_links = FALSE;
                }
                foreach ($links[$tab_obj->id] as $link_obj){
                    $laction = array();
                    if ($move_links){
                        $lvalues['link_id'] = $link_obj->id;
                        $lvalues['command'] = 'link_up';
                        $laction[] = PHPWS_Text::moduleLink($up_link, 'controlpanel', $lvalues);

                        $lvalues['command'] = 'link_down';
                        $laction[] = PHPWS_Text::moduleLink($down_link, 'controlpanel', $lvalues);
                    }

                    $lvalues['command'] = 'edit_link';
                    $jslink['address'] = PHPWS_Text::linkAddress('controlpanel', $lvalues);
                    $jslink['label'] = dgettext('controlpanel', 'Edit');
                    $jslink['width'] = 360;
                    $jslink['height'] = 350;
                    $edit_link = javascript('open_window', $jslink, true);

                    $tpl->setCurrentBlock('link-list');
                    $tpl->setData(array('LINK'=>$link_obj->getLabel(), 'LACTION'=>implode('', $laction),
                                        'EDIT_LINK' =>  $edit_link));
                    $tpl->parseCurrentBlock();
                }
            }

            if ($move_tabs){
                $tvalues['tab_id'] = $tab_obj->id;
                $tvalues['command'] = 'tab_up';
                $taction[] = PHPWS_Text::secureLink($up_tab, 'controlpanel', $tvalues);

                $tvalues['command'] = 'tab_down';
                $taction[] = PHPWS_Text::secureLink($down_tab, 'controlpanel', $tvalues);
            }

            $tvalues['command'] = 'edit_tab_title';
            $jstab['address'] = PHPWS_Text::linkAddress('controlpanel', $tvalues);
            $jstab['label'] = dgettext('controlpanel', 'Edit');
            $jstab['width'] = 260;
            $jstab['height'] = 180;
            $edit_tab = javascript('open_window', $jstab, true);

            $tpl->setCurrentBlock('tab-list');
            $tpl->setData(array('TAB'=>$tab_obj->getTitle(), 'TACTION'=>implode('', $taction), 'EDIT_TAB' => $edit_tab));
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        return $content;
    }

    public function editTabTitle($tab)
    {
        if (!$tab->id) {
            return false;
        }
        $form = new PHPWS_Form;
        $form->addHidden('module', 'controlpanel');
        $form->addHidden('command', 'post_tab');
        $form->addHidden('action', 'admin');
        $form->addHidden('tab_id', $tab->id);

        $form->addText('title', $tab->title);
        $form->setLabel('title', dgettext('controlpanel', 'Title'));

        $form->addSubmit(dgettext('controlpanel', 'Save'));
        $tpl = $form->getTemplate();

        $tpl['CLOSE'] = javascript('close_window');
        $tpl['FORM_TITLE'] = dgettext('controlpanel', 'Edit tab');
        return PHPWS_Template::process($tpl, 'controlpanel', 'tab_form.tpl');
    }

    public function editLink($link)
    {
        if (!$link->id) {
            return false;
        }
        $form = new PHPWS_Form;
        $form->addHidden('module', 'controlpanel');
        $form->addHidden('command', 'post_link');
        $form->addHidden('action', 'admin');
        $form->addHidden('link_id', $link->id);

        $form->addText('label', $link->label);
        $form->setLabel('label', dgettext('controlpanel', 'Label'));

        $form->addTextArea('description', $link->description);
        $form->setLabel('description', dgettext('controlpanel', 'Description'));

        $form->addSubmit(dgettext('controlpanel', 'Save'));
        $tpl = $form->getTemplate();

        $tpl['CLOSE'] = javascript('close_window');
        $tpl['FORM_TITLE'] = dgettext('controlpanel', 'Edit link');
        return PHPWS_Template::process($tpl, 'controlpanel', 'link_form.tpl');
    }
}

?>