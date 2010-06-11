<?php
/**
 * sitemap - phpwebsite module
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

class Sitemap_Forms {
    public $sitemap = null;


    public function get($type)
    {
        switch ($type) {

            case 'new':
                $this->mapSetup();
                break;

            case 'settings':
                $this->sitemap->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'info':
                $this->sitemap->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    public function mapSetup()
    {
        $form = new PHPWS_Form('sitemap');
        $form->addHidden('module', 'sitemap');
        $form->addHidden('aop', 'make_map');
        $form->addSubmit(dgettext('sitemap', 'Build'));
        if (@$_REQUEST['build_type']) {
            $bMatch = $_REQUEST['build_type'];
        } else {
            $bMatch = 0;
        }
        if (@$_REQUEST['menus']) {
            $mMatch = $_REQUEST['menus'];
        } else {
            $mMatch = null;
        }
        if (@$_REQUEST['exclude_keys']) {
            $kMatch = $_REQUEST['exclude_keys'];
        } else {
            $kMatch = unserialize(PHPWS_Settings::get('sitemap', 'exclude_keys'));
        }
        if (@$_REQUEST['lastmod_default']) {
            $dMatch = $_REQUEST['lastmod_default'];
        } else {
            $dMatch = null;
        }

        $form->addCheckbox('addkeys', 1);
        $form->setMatch('addkeys', PHPWS_Settings::get('sitemap', 'addkeys'));
        $form->setLabel('addkeys', dgettext('sitemap', 'Include keyed module items not in menus in your sitemap'));

        $form->addRadio('build_type', array(0, 1));
        $form->setLabel('build_type', array(dgettext('sitemap', 'Download'), sprintf(dgettext('sitemap', 'Save to server (%s)'), PHPWS_Core::getHomeHttp() . 'sitemap.xml')));
        $form->setMatch('build_type', $bMatch);

        if (PHPWS_Settings::get('sitemap', 'use_lastmod')) {
            $form->addText('lastmod_default', $dMatch);
            $form->setLabel('lastmod_default', dgettext('sitemap', 'Lastmod date for non-keyed items (YYYY-MM-DD) or leave empty'));
            $form->setSize('lastmod_default', 30);
        }

        $tpl = $form->getTemplate();
        $tpl['OPTIONS_LABEL'] = dgettext('sitemap', 'Options');
        $tpl['MENU_SELECT'] = $this->sitemap->getMenus($mMatch, 'menus');
        $tpl['MENU_SELECT_LABEL'] = dgettext('sitemap', 'Select one or more menus to include in your sitemap.');
        $tpl['KEY_SELECT'] = $this->sitemap->getKeyMods($kMatch, 'exclude_keys');
        $tpl['KEY_SELECT_LABEL'] = dgettext('sitemap', 'Select any modules you wish to exclude from your sitemap.');

        $this->sitemap->title = dgettext('sitemap', 'Create sitemap file');
        $this->sitemap->content = PHPWS_Template::process($tpl, 'sitemap', 'map_settings.tpl');
    }


    public function editSettings()
    {

        $form = new PHPWS_Form('sitemap_settings');
        $form->addHidden('module', 'sitemap');
        $form->addHidden('aop', 'post_settings');

        $form->addCheckbox('respect_privs', 1);
        $form->setMatch('respect_privs', PHPWS_Settings::get('sitemap', 'respect_privs'));
        $form->setLabel('respect_privs', dgettext('sitemap', 'Respect keyed menu item permissions'));

        $form->addCheckbox('local_only', 1);
        $form->setMatch('local_only', PHPWS_Settings::get('sitemap', 'local_only'));
        $form->setLabel('local_only', dgettext('sitemap', 'Exclude remote URLs'));

        $form->addCheckbox('use_change', 1);
        $form->setMatch('use_change', PHPWS_Settings::get('sitemap', 'use_change'));
        $form->setLabel('use_change', dgettext('sitemap', 'Add optional* change frequency to sitemap'));

        $freqs= array('1'=>'always', '2'=>'hourly', '3'=>'daily', '4'=>'weekly', '5'=>'monthly', '6'=>'yearly', '7'=>'never');
        $form->addSelect('change_freq', $freqs);
        $form->setMatch('change_freq', PHPWS_Settings::get('sitemap', 'change_freq'));
        $form->setLabel('change_freq', dgettext('sitemap', 'Change frequency'));

        $form->addCheckbox('use_lastmod', 1);
        $form->setMatch('use_lastmod', PHPWS_Settings::get('sitemap', 'use_lastmod'));
        $form->setLabel('use_lastmod', dgettext('sitemap', 'Add optional* lastmod date to sitemap'));

        $form->addCheckbox('use_priority', 1);
        $form->setMatch('use_priority', PHPWS_Settings::get('sitemap', 'use_priority'));
        $form->setLabel('use_priority', dgettext('sitemap', 'Add optional* priority** to sitemap'));

        $form->addCheckbox('allow_feed', 1);
        $form->setMatch('allow_feed', PHPWS_Settings::get('sitemap', 'allow_feed'));
        $form->setLabel('allow_feed', dgettext('sitemap', 'Allow live xml feed*** of all non-restricted menus'));

        $form->addCheckbox('addkeys', 1);
        $form->setMatch('addkeys', PHPWS_Settings::get('sitemap', 'addkeys'));
        $form->setLabel('addkeys', dgettext('sitemap', 'Include keyed items not in menus in your sitemap'));

        $form->addText('cache_timeout', PHPWS_Settings::get('sitemap', 'cache_timeout'));
        $form->setSize('cache_timeout', 4, 4);
        $form->setLabel('cache_timeout', dgettext('sitemap', 'Cache duration in seconds for xml feed (0-7200, set to 0 to disable cache)'));

        $form->addSubmit('save', dgettext('sitemap', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('sitemap', 'General Settings');
        $tpl['KEY_SELECT'] = $this->sitemap->getKeyMods(unserialize(PHPWS_Settings::get('sitemap', 'exclude_keys')), 'exclude_keys');
        $tpl['KEY_SELECT_LABEL'] = dgettext('sitemap', 'Select any modules you wish to exclude by default from your sitemap.');
        $tpl['FOOTNOTE_1'] = sprintf(dgettext('sitemap', '* More information on the Sitemap protocal is available %s'), '<a href="http://www.sitemaps.org/protocol.php" target="new">'. dgettext('sitemap', 'here') .'</a>');
        $tpl['FOOTNOTE_2'] = dgettext('sitemap', '** I do a rough calculation to achieve this. Everything starts as the average 0.5. I add 0.5 to top-level itmes. I then subtract 0.1 for each step down the menu order. Nothing is scored above 1.0 or below 0.1.');
        $tpl['FOOTNOTE_3'] = dgettext('sitemap', '*** The feed respects the settings above. Lastmod date of non-keyed items will be null.');

        $this->sitemap->title = dgettext('sitemap', 'Settings');
        $this->sitemap->content = PHPWS_Template::process($tpl, 'sitemap', 'edit_settings.tpl');
    }


    public function showInfo()
    {

        $tpl['TITLE'] = dgettext('sitemap', 'Important Information');
        $tpl['INFO_1_LABEL'] = dgettext('sitemap', 'About this module:');
        $tpl['INFO_1'] = dgettext('sitemap', 'This is the third release of sitemap for the new 1.x series phpwebsite. I wrote this for a specific need I had and thought it may be of use to others also.');
        $tpl['INFO_2_LABEL'] = dgettext('sitemap', 'What it does:');
        $tpl['INFO_2'] = sprintf(dgettext('sitemap', 'Sitemap is used to generate a sitmap.xml file, following the standards at %s. These sitemap files are used by google and other popular search engines to help them index your website. Sitemap does this by gathering all the links from one or more of your menus, as well as other keyed module items (such as blog posts) that may not be in a menu, filtering and preparing according to your settings, and then writing the xml for you.'), '<a href="http://www.sitemaps.org/protocol.php" target="new">sitemaps.org</a>');
        $tpl['INFO_3_LABEL'] = dgettext('sitemap', 'How to use it:');
        if (MOD_REWRITE_ENABLED) {
            $modlink = 'sitemap';
        } else {
            $modlink = 'index.php?module=sitemap';
        }
        $tpl['INFO_3'] = sprintf(dgettext('sitemap', 'First, review the settings. Then, you can either download a sitemap file for using later, save a sitemap file to the server at %s, or allow a live and dynamic sitemap feed at %s'), PHPWS_Core::getHomeHttp() . 'sitemap.xml', PHPWS_Core::getHomeHttp() . $modlink);
        $tpl['INFO_4_LABEL'] = null;
        $tpl['INFO_4'] = null;
        $tpl['INFO_5_LABEL'] = null;
        $tpl['INFO_5'] = null;
        $tpl['DONATE'] = sprintf(dgettext('sitemap', 'If you would like to help out with the ongoing development of sitemap, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Sitemap%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->sitemap->title = dgettext('sitemap', 'Read me');
        $this->sitemap->content = PHPWS_Template::process($tpl, 'sitemap', 'info.tpl');
    }


}

?>