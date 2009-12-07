<?php
/**
    * vpath - phpwebsite module
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
    * @version $Id$
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

class vPath_Forms {
    public $vpath = null;

    public function get($type)
    {
        switch ($type) {

        case 'settings':
            $this->vpath->panel->setCurrentTab('settings');
            $this->editSettings();
            break;

        case 'info':
            $this->vpath->panel->setCurrentTab('info');
            $this->showInfo();
            break;

        }

    }


    public function editSettings()
    {

        $form = new PHPWS_Form('vpath_settings');
        $form->addHidden('module', 'vpath');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('enable_path', 1);
        $form->setMatch('enable_path', PHPWS_Settings::get('vpath', 'enable_path'));
        $form->setLabel('enable_path', dgettext('vpath', 'Enable vpath'));

        $form->addCheckbox('show_on_home', 1);
        $form->setMatch('show_on_home', PHPWS_Settings::get('vpath', 'show_on_home'));
        $form->setLabel('show_on_home', dgettext('vpath', 'Display path on home'));

        $db = new PHPWS_DB('menus');
        $db->addOrder('title asc');
        $result = $db->select();
        foreach ($result as $menu) {
            $menus[$menu['id']] = $menu['title'];
        }
        $form->addSelect('menu_id', $menus);
        $form->setMatch('menu_id', PHPWS_Settings::get('vpath', 'menu_id'));
        $form->setLabel('menu_id', dgettext('vpath', 'Menu to follow'));
    
        require(PHPWS_SOURCE_DIR . 'mod/vpath/inc/dividers.php');
        $form->addSelect('divider', $vpath_dividers);
        $form->setMatch('divider', PHPWS_Settings::get('vpath', 'divider'));
        $form->setLabel('divider', dgettext('vpath', 'Divider'));
    
        $form->addCheckbox('divider_space', 1);
        $form->setMatch('divider_space', PHPWS_Settings::get('vpath', 'divider_space'));
        $form->setLabel('divider_space', dgettext('vpath', 'Use space around divider'));

        $form->addText('path_prefix', PHPWS_Settings::get('vpath', 'path_prefix'));
        $form->setSize('path_prefix', 30);
        $form->setLabel('path_prefix', dgettext('vpath', 'Text before the path, eg. You are here:'));

        $form->addText('path_suffix', PHPWS_Settings::get('vpath', 'path_suffix'));
        $form->setSize('path_suffix', 30);
        $form->setLabel('path_suffix', dgettext('vpath', 'Text after the path'));

        $form->addCheckbox('link_current', 1);
        $form->setMatch('link_current', PHPWS_Settings::get('vpath', 'link_current'));
        $form->setLabel('link_current', dgettext('vpath', 'Make current location (end of path) clickable'));

        $form->addCheckbox('show_sub_menu', 1);
        $form->setMatch('show_sub_menu', PHPWS_Settings::get('vpath', 'show_sub_menu'));
        $form->setLabel('show_sub_menu', dgettext('vpath', 'Display sub menu for current location'));

        $form->addSubmit('save', dgettext('vpath', 'Save settings'));
        
        $tpl = $form->getTemplate();
        $tpl['GENERAL_LABEL'] = dgettext('vpath', 'General Settings');

        $this->vpath->title = dgettext('vpath', 'Settings');
        $this->vpath->content = PHPWS_Template::process($tpl, 'vpath', 'edit_settings.tpl');
    }


    public function showInfo()
    {
        
        $filename = 'mod/vpath/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('vpath', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('vpath', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('vpath', 'If you would like to help out with the ongoing development of vpath, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=vPath%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->vpath->title = dgettext('vpath', 'Read me');
        $this->vpath->content = PHPWS_Template::process($tpl, 'vpath', 'info.tpl');
    }



}

?>