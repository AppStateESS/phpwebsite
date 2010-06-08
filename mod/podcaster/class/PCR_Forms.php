<?php
/**
 * podcaster - phpwebsite module
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

class Podcaster_Forms {
    var $podcaster = null;

    function get($type)
    {
        switch ($type) {

            case 'new':
            case 'edit_channel':
                if (empty($this->podcaster->channel)) {
                    $this->podcaster->loadChannel();
                }
                $this->editChannel();
                break;

            case 'edit_episode':
                $this->editEpisode();
                break;

            case 'list':
                $this->podcaster->panel->setCurrentTab('list');
                $this->listChannels();
                break;

            case 'episodes':
                $this->podcaster->panel->setCurrentTab('episodes');
                $this->listEpisodes(1);
                break;

            case 'approvals':
                $this->podcaster->panel->setCurrentTab('approvals');
                $this->listEpisodes(0);
                break;

            case 'settings':
                $this->podcaster->panel->setCurrentTab('settings');
                $this->editSettings();
                break;

            case 'info':
                $this->podcaster->panel->setCurrentTab('info');
                $this->showInfo();
                break;

        }

    }


    function editChannel()
    {
        $form = new \core\Form('podcaster_channel');
        $channel = & $this->podcaster->channel;

        $form->addHidden('module', 'podcaster');
        $form->addHidden('aop', 'post_channel');
        if ($channel->id) {
            $form->addHidden('id', $channel->id);
            $form->addSubmit(dgettext('podcaster', 'Update'));
            $this->podcaster->title = dgettext('podcaster', 'Update podcaster channel');
        } else {
            $form->addSubmit(dgettext('podcaster', 'Create'));
            $this->podcaster->title = dgettext('podcaster', 'Create podcaster channel');
        }

        $form->addText('title', $channel->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('podcaster', 'Title'));

        $form->addTextArea('description', $channel->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('podcaster', 'Description'));

        \core\Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('image_id', $channel->image_id);
        $manager->imageOnly();
        $manager->maxImageWidth(core\Settings::get('podcaster', 'max_width'));
        $manager->maxImageHeight(core\Settings::get('podcaster', 'max_height'));

        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        if (Current_User::isUnrestricted('podcaster')) {
            $form->addCheck('active', 1);
            $form->setLabel('active', dgettext('podcaster', 'Active'));
            $form->setMatch('active', $channel->active);
        }

        $choices = array ('0' => dgettext('podcaster', 'Audio/Video'), '1' => dgettext('podcaster', 'Document'));
        $form->addSelect('media_type', $choices);
        $form->setLabel('media_type', dgettext('podcaster', 'Media type for this channel'));
        $form->setMatch('media_type', $channel->getMedia_type());

        $choices = array ('0' => dgettext('podcaster', 'No'), '1' => dgettext('podcaster', 'Yes'), '2' => dgettext('podcaster', 'Clean'));
        $form->addSelect('itunes_explicit', $choices);
        $form->setLabel('itunes_explicit', dgettext('podcaster', 'iTunes Explicit'));
        $form->setMatch('itunes_explicit', $channel->getItunes_explicit());

        \core\Core::initModClass('podcaster', 'PCR_Category.php');
        $db = new \core\DB('podcaster_category');
        $db->addOrder('id asc');
        $result = $db->getObjects('Podcaster_Category');
        foreach ($result as $cat) {
            if ($cat->parent_id > 0) {
                $iTunesCats[$cat->id] = ' - ' . $cat->title;
            } else {
                $iTunesCats[$cat->id] = $cat->title;
            }
        }

        $catsMatch = $channel->itunes_category;
        $form->addMultiple('itunes_category', $iTunesCats);
        $form->setLabel('itunes_category', dgettext('podcaster', 'iTunes Category(s)'));
        $form->setMatch('itunes_category', $catsMatch);

        $tpl = $form->getTemplate();
        $tpl['DETAILS_LABEL'] = dgettext('podcaster', 'Details');
        $tpl['SETTINGS_LABEL'] = dgettext('podcaster', 'Settings');
        $tpl['ITUNES_INFO_LABEL'] = dgettext('podcaster', 'A note on iTunes categories:');
        $tpl['ITUNES_INFO'] = dgettext('podcaster', 'iTunes categories are used to classify your podcasts in the iTunes podcast directory. They are limited and specific. Although it is OK to leave this field empty, choosing the correct categories is good for your feed and may be used by other podcast directories and readers too.');

        $this->podcaster->content = \core\Template::process($tpl, 'podcaster', 'edit_channel.tpl');
    }


    function editEpisode()
    {
        $form = new \core\Form;
        $form->addHidden('module', 'podcaster');
        $form->addHidden('aop', 'post_episode');
        $form->addHidden('channel_id', $this->podcaster->channel->id);
        if ($this->podcaster->episode->id) {
            $this->podcaster->title = sprintf(dgettext('podcaster', 'Update %s episode'), $this->podcaster->channel->title);
            $form->addHidden('episode_id', $this->podcaster->episode->id);
            $form->addSubmit(dgettext('podcaster', 'Update'));
        } else {
            $this->podcaster->title = sprintf(dgettext('podcaster', 'Add episode to %s'), $this->podcaster->channel->title);
            $form->addSubmit(dgettext('podcaster', 'Add'));
        }

        $form->addText('title', $this->podcaster->episode->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('podcaster', 'Title'));

        $form->addTextArea('description', $this->podcaster->episode->description);
        $form->setRows('description', '6');
        $form->setCols('description', '40');
        $form->setLabel('description', dgettext('podcaster', 'Description'));

        \core\Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager('media_id', $this->podcaster->episode->media_id);
        if ($this->podcaster->channel->media_type == 0) {
            $manager->mediaOnly();
        } elseif ($this->podcaster->channel->media_type == 1) {
            $manager->documentOnly();
        }

        if ($manager) {
            $form->addTplTag('FILE_MANAGER', $manager->get());
        }

        if (Current_User::isUnrestricted('podcaster')) {
            $form->addCheck('active', 1);
            $form->setLabel('active', dgettext('podcaster', 'Active'));
            $form->setMatch('active', $this->podcaster->episode->active);

            $form->addCheck('approved', 1);
            $form->setLabel('approved', dgettext('podcaster', 'Approved'));
            $form->setMatch('approved', $this->podcaster->episode->approved);
        }

        $tpl = $form->getTemplate();
        $tpl['INFO_LABEL'] = dgettext('podcaster', 'Information');
        $tpl['MEDIA_LABEL'] = dgettext('podcaster', 'Media');
        if (Current_User::isUnrestricted('podcaster')) {
            $tpl['SETTINGS_LABEL'] = dgettext('podcaster', 'Settings');
        }

        $this->podcaster->content = \core\Template::process($tpl, 'podcaster', 'edit_episode.tpl');
    }


    function listChannels()
    {
        $ptags['TITLE_HEADER'] = dgettext('podcaster', 'Title');
        $ptags['DATE_UPDATED_HEADER'] = dgettext('podcaster', 'Updated');

        \core\Core::initModClass('podcaster', 'PCR_Channel.php');
                $pager = new \core\DBPager('podcaster_channel', 'Podcaster_Channel');
        $pager->setModule('podcaster');
        if (!Current_User::isUnrestricted('podcaster')) {
            $pager->addWhere('active', 1);
        }
        $pager->setOrder('title', 'asc', true);
        $pager->setTemplate('list_channel.tpl');
        $pager->addRowTags('rowTag');
        $num = $pager->getTotalRows();
        if ($num == '0') {
            if (Current_User::allow('podcaster', 'edit_channel')) {
                $vars['aop']  = 'menu';
                $vars['tab']  = 'settings';
                $vars2['aop']  = 'edit_channel';
                $ptags['EMPTY_MESSAGE'] = sprintf(dgettext('podcaster', 'Check your %s then create a %s to begin'), \core\Text::secureLink(dgettext('podcaster', 'Settings'), 'podcaster', $vars),  \core\Text::secureLink(dgettext('podcaster', 'New Channel'), 'podcaster', $vars2));
            } else {
                $ptags['EMPTY_MESSAGE'] = dgettext('podcaster', 'Sorry, no channels are available at this time.');
            }
        }
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

        $this->podcaster->content = $pager->get();
        $this->podcaster->title = dgettext('podcaster', 'Podcaster Channels');
    }


    function listEpisodes($approved=null,$channel_id=null)
    {
        $ptags['TITLE_HEADER'] = dgettext('podcaster', 'Title');
        $ptags['DATE_UPDATED_HEADER'] = dgettext('podcaster', 'Updated');
        $ptags['CHANNEL_HEADER'] = dgettext('podcaster', 'Channel');

        \core\Core::initModClass('podcaster', 'PCR_Episode.php');
                $pager = new \core\DBPager('podcaster_episode', 'Podcaster_Episode');
        $pager->setModule('podcaster');
        if (isset($approved)) {
            $pager->addWhere('approved', $approved);
        }
        if (isset($channel_id)) {
            $pager->addWhere('channel_id', $channel_id);
        }
        if (!Current_User::isUnrestricted('podcaster')) {
            $pager->addWhere('active', 1);
        }
        $pager->setOrder('title', 'asc', true);
        if (!isset($channel_id)) {
            $pager->setTemplate('list_episode.tpl');
        } else {
            $pager->setTemplate('list_episode_channel.tpl');
        }
        $pager->addRowTags('rowTag');
        $pager->addPageTags($ptags);
        $pager->addToggle('class="toggle1"');
        $pager->setSearch('title', 'description');

        if (isset($channel_id)) {
            \core\Core::initModClass('podcaster', 'PCR_Channel.php');
            $channel = new Podcaster_Channel($channel_id);
            $this->podcaster->title = sprintf(dgettext('podcaster', 'All %s Episodes'), $channel->viewLink());
        } else {
            $this->podcaster->title = dgettext('podcaster', 'All Podcaster Episodes');
        }

//        $pager->db->setTestMode();
        $this->podcaster->content = $pager->get();
    }


    function editSettings()
    {

        $form = new \core\Form('podcaster_settings');
        $form->addHidden('module', 'podcaster');
        $form->addHidden('aop', 'post_settings');

        $form->addText('channel_limit', \core\Settings::get('podcaster', 'channel_limit'));
        $form->setSize('channel_limit', 2, 2);
        $form->setLabel('channel_limit', dgettext('podcaster', 'Show current episodes per channel limit (1-50)'));

        $form->addText('cache_timeout', \core\Settings::get('podcaster', 'cache_timeout'));
        $form->setSize('cache_timeout', 4, 4);
        $form->setLabel('cache_timeout', dgettext('podcaster', 'Cache duration in seconds for rss feed (0-7200, set to 0 to disable cache)'));

        $form->addCheckbox('show_block', 1);
        $form->setMatch('show_block', \core\Settings::get('podcaster', 'show_block'));
        $form->setLabel('show_block', dgettext('podcaster', 'Show podcaster block'));

        $form->addRadio('block_order_by_rand', array(0, 1));
        $form->setLabel('block_order_by_rand', array(dgettext('podcaster', 'Most recent'), dgettext('podcaster', 'Random')));
        $form->setMatch('block_order_by_rand', \core\Settings::get('podcaster', 'block_order_by_rand'));

        $form->addCheckbox('block_on_home_only', 1);
        $form->setMatch('block_on_home_only', \core\Settings::get('podcaster', 'block_on_home_only'));
        $form->setLabel('block_on_home_only', dgettext('podcaster', 'Show on home only'));

        $form->addCheckbox('req_approval', 1);
        $form->setMatch('req_approval', \core\Settings::get('podcaster', 'req_approval'));
        $form->setLabel('req_approval', dgettext('podcaster', 'Require approval for new episodes'));

        $form->addText('editor', \core\Settings::get('podcaster', 'editor'));
        $form->setLabel('editor', dgettext('podcaster', 'Managing editor email address'));
        $form->setSize('editor', 30);

        $form->addText('webmaster', \core\Settings::get('podcaster', 'webmaster'));
        $form->setLabel('webmaster', dgettext('podcaster', 'Webmaster email address'));
        $form->setSize('webmaster', 30);

        $form->addText('copyright', \core\Settings::get('podcaster', 'copyright'));
        $form->setLabel('copyright', dgettext('podcaster', 'Copyright'));
        $form->setSize('copyright', 40);

        $form->addCheckbox('rm_media', 1);
        $form->setMatch('rm_media', \core\Settings::get('podcaster', 'rm_media'));
        $form->setLabel('rm_media', dgettext('podcaster', 'Delete media from filecabinet when deleting episode'));

        $form->addTextField('max_width', \core\Settings::get('podcaster', 'max_width'));
        $form->setLabel('max_width', dgettext('podcaster', 'Maximum image width (50-600)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', \core\Settings::get('podcaster', 'max_height'));
        $form->setLabel('max_height', dgettext('podcaster', 'Maximum image height (50-600)'));
        $form->setSize('max_height', 4,4);

        $form->addCheck('mod_folders_only', 1);
        $form->setLabel('mod_folders_only', dgettext('podcaster', 'Hide general image folders'));
        $form->setMatch('mod_folders_only', \core\Settings::get('podcaster', 'mod_folders_only'));

        $form->addSubmit('save', dgettext('podcaster', 'Save settings'));

        $tpl = $form->getTemplate();
        $tpl['SETTINGS_LABEL'] = dgettext('podcaster', 'General Settings');
        $tpl['IMAGE_LABEL'] = dgettext('podcaster', 'Image Settings');

        $this->podcaster->title = dgettext('podcaster', 'Settings');
        $this->podcaster->content = \core\Template::process($tpl, 'podcaster', 'edit_settings.tpl');
    }


    function showInfo()
    {

        $filename = 'mod/podcaster/docs/README';
        if (@fopen($filename, "rb")) {
            $handle = fopen($filename, "rb");
            $readme = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $readme = dgettext('podcaster', 'Sorry, the readme file does not exist.');
        }

        $tpl['TITLE'] = dgettext('podcaster', 'Important Information');
        $tpl['INFO'] = $readme;
        $tpl['DONATE'] = sprintf(dgettext('podcaster', 'If you would like to help out with the ongoing development of Podcaster, or other modules by Verdon Vaillancourt, %s click here to donate %s (opens in new browser window).'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donations%40verdon%2eca&item_name=Podcaster%20Module%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=CA&bn=PP%2dDonationsBF&charset=UTF%2d8" target="new">', '</a>');

        $this->podcaster->title = dgettext('podcaster', 'Read me');
        $this->podcaster->content = \core\Template::process($tpl, 'podcaster', 'info.tpl');
    }


}

?>