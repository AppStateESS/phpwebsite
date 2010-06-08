<?php
/**
 * whatsnew - phpwebsite module
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

class Whatsnew_Forms {
    var $whatsnew = null;

    function get($type)
    {
        switch ($type) {

            case 'settings':
                $this->whatsnew->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'info':
                $this->whatsnew->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    function editSettings()
    {

        $form = new \core\Form('whatsnew_settings');
        $form->addHidden('module', 'whatsnew');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('enable', 1);
        $form->setMatch('enable', \core\Settings::get('whatsnew', 'enable'));
        $form->setLabel('enable', dgettext('whatsnew', 'Enable whatsnew'));

        $form->addCheckbox('homeonly', 1);
        $form->setMatch('homeonly', \core\Settings::get('whatsnew', 'homeonly'));
        $form->setLabel('homeonly', dgettext('whatsnew', 'Show whatsnew sidebox on home page only'));

        $form->addTextField('title', \core\Settings::get('whatsnew', 'title'));
        $form->setLabel('title', dgettext('whatsnew', 'Sidebox title'));
        $form->setSize('title', 30);

        $form->addTextArea('text', \core\Settings::get('whatsnew', 'text'));
        $form->setRows('text', '4');
        $form->setCols('text', '40');
        $form->setLabel('text', dgettext('whatsnew', 'Sidebox text'));

        $form->addTextField('cache_timeout', \core\Settings::get('whatsnew', 'cache_timeout'));
        $form->setLabel('cache_timeout', dgettext('whatsnew', 'Cache duration for whatsnew list (in seconds, 0-7200)'));
        $form->setSize('cache_timeout', 4,4);

        $form->addTextField('qty_items', \core\Settings::get('whatsnew', 'qty_items'));
        $form->setLabel('qty_items', dgettext('whatsnew', 'Number of recent items to display (0-50)'));
        $form->setSize('qty_items', 4,4);

        $form->addCheckbox('show_summaries', 1);
        $form->setMatch('show_summaries', \core\Settings::get('whatsnew', 'show_summaries'));
        $form->setLabel('show_summaries', dgettext('whatsnew', 'Show item summaries'));

        $form->addCheckbox('show_dates', 1);
        $form->setMatch('show_dates', \core\Settings::get('whatsnew', 'show_dates'));
        $form->setLabel('show_dates', dgettext('whatsnew', 'Show item update dates'));

        $form->addCheckbox('show_source_modules', 1);
        $form->setMatch('show_source_modules', \core\Settings::get('whatsnew', 'show_source_modules'));
        $form->setLabel('show_source_modules', dgettext('whatsnew', 'Show item source module names'));

        $form->addSubmit('save', dgettext('whatsnew', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('whatsnew', 'General Settings');
        $tpl['FLUSH_LINK'] = \core\Text::secureLink(dgettext('whatsnew', 'Flush cache'), 'whatsnew', array('aop'=>'flush_cache'));
        $tpl['EXCLUDE'] = $this->whatsnew->getKeyMods(unserialize(core\Settings::get('whatsnew', 'exclude')), 'exclude');
        $tpl['EXCLUDE_LABEL'] = dgettext('whatsnew', 'Select any modules you wish to exclude from your whatsnew box.');

        $this->whatsnew->title = dgettext('whatsnew', 'Settings');
        $this->whatsnew->content = \core\Template::process($tpl, 'whatsnew', 'edit_settings.tpl');
    }


    function showInfo()
    {

        $filename = 'mod/whatsnew/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('whatsnew', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('whatsnew', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('whatsnew', 'If you would like to help out with the ongoing development of whatsnew, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Whatsnew%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->whatsnew->title = dgettext('whatsnew', 'Read me');
        $this->whatsnew->content = \core\Template::process($tpl, 'whatsnew', 'info.tpl');
    }



}

?>