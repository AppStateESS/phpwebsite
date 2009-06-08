<?php
/**
    * finc - phpwebsite module
    *
    * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
    *
    * This program is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    * 
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    * 
    * You should have received a copy of the GNU General Public License
    * along with this program; if not, write to the Free Software
    * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    *
    * @version $Id: $
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

class Finc_Forms {
    var $finc = null;

    function get($type)
    {
        switch ($type) {

        case 'new':
        case 'edit_file':
            if (empty($this->finc->file)) {
                $this->finc->loadFile();
            }
            $this->editFile();
            break;

        case 'list':
            $this->finc->panel->setCurrentTab('list');
            $this->listFiles();
            break;

        case 'settings':
            $this->finc->panel->setCurrentTab('settings');
            $this->editSettings();
            break;

        case 'info':
            $this->finc->panel->setCurrentTab('info');
            $this->showInfo();
            break;

        }

    }


    function editFile()
    {
        $form = new PHPWS_Form('finc_file');
        $file = & $this->finc->file;

        $form->addHidden('module', 'finc');
        $form->addHidden('aop', 'post_file');
        if ($file->id) {
            $form->addHidden('id', $file->id);
            $form->addSubmit(dgettext('finc', 'Update'));
            $this->finc->title = dgettext('finc', 'Update finc file');
        } else {
            $form->addSubmit(dgettext('finc', 'Create'));
            $this->finc->title = dgettext('finc', 'Create finc file');
        }

        $form->addText('title', $file->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('finc', 'Title'));

        if ($file->id) {
            $form->addText('path', $file->path);
        } else {
            $form->addText('path', 'files/finc/');
        }
        $form->setSize('path', 40);
        $form->setLabel('path', dgettext('finc', 'File path/name'));

        $form->addTextArea('description', $file->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('finc', 'Description'));

        $form->addCheck('active', 1);
        $form->setLabel('active', dgettext('finc', 'Active'));
        $form->setMatch('active', $file->active);
        
        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('finc', 'Details');

        $this->finc->content = PHPWS_Template::process($tpl, 'finc', 'edit_file.tpl');
    }


    function listFiles()
    {
        $ptags['TITLE_HEADER'] = dgettext('finc', 'Title');
        $ptags['PATH_HEADER'] = dgettext('finc', 'Path/filename');

        PHPWS_Core::initModClass('finc', 'FINC_File.php');
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('finc_file', 'Finc_File');
        $pager->setModule('finc');
        if (!Current_User::isUnrestricted('finc')) {
            $pager->addWhere('active', 1);
        }
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_files.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            $vars['aop']  = 'menu';
            $vars['tab']  = 'settings';
            $vars2['aop']  = 'edit_file';
            $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('finc', 'Check your %s then create a %s to begin'), PHPWS_Text::secureLink(dgettext('finc', 'Settings'), 'finc', $vars),  PHPWS_Text::secureLink(dgettext('finc', 'New File'), 'finc', $vars2));
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'path', 'description');

        $this->finc->content = $pager->get();
        $this->finc->title = dgettext('finc', 'Finc Files');
    }


    function editSettings()
    {

        $form = new PHPWS_Form('finc_settings');
        $form->addHidden('module', 'finc');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('show_title', 1);
        $form->setMatch('show_title', PHPWS_Settings::get('finc', 'show_title'));
        $form->setLabel('show_title', dgettext('finc', 'Show title'));

        $form->addCheckbox('add_title_tag', 1);
        $form->setMatch('add_title_tag', PHPWS_Settings::get('finc', 'add_title_tag'));
        $form->setLabel('add_title_tag', dgettext('finc', 'Add title to title meta tag'));

        $form->addCheckbox('show_description', 1);
        $form->setMatch('show_description', PHPWS_Settings::get('finc', 'show_description'));
        $form->setLabel('show_description', dgettext('finc', 'Show description'));

        $form->addSubmit('save', dgettext('finc', 'Save settings'));
        
        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('finc', 'General Settings');

        $this->finc->title = dgettext('finc', 'Settings');
        $this->finc->content = PHPWS_Template::process($tpl, 'finc', 'edit_settings.tpl');
    }


    function showInfo()
    {
        
        $filename = 'mod/finc/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('finc', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('finc', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('finc', 'If you would like to help out with the ongoing development of finc, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Finc%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->finc->title = dgettext('finc', 'Read me');
        $this->finc->content = PHPWS_Template::process($tpl, 'finc', 'info.tpl');
    }


}

?>