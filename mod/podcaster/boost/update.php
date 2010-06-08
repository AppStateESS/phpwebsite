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

function podcaster_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_channel.tpl');
            podcasterUpdateFiles($files, $content);

            $content[] = '1.0.1 changes
----------------
+ Added complete iTunes category examples to add/edit channel form 
</pre>';


        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>';

            $files = array('templates/list_episode.tpl',
                       'templates/list_episode_channel.tpl',
                       'templates/list_channel.tpl'
                       );
            podcasterUpdateFiles($files, $content);

            $content[] = '1.0.2 changes
----------------
+ Added paged archive view
+ Fixed paged channel list
+ Fixed episode limit in channel view

</pre>';


        case version_compare($currentVersion, '1.0.3', '<'):
            $content[] = '<pre>';

            $files = array('templates/uninstall.tpl'
            );
            podcasterUpdateFiles($files, $content);

            $content[] = '1.0.3 changes
----------------
+ Added option to delete media files from filecabinet when uninstalling
+ Version numbering corrected
+ Entered into CVS

</pre>';


        case version_compare($currentVersion, '1.0.4', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl',
                       'templates/edit_channel.tpl',
                       'templates/edit_episode.tpl',
                       'templates/list_channel.tpl',
                       'templates/list_episode.tpl',
                       'templates/list_episode_channel.tpl',
                       'templates/view_rss.tpl',
                       'templates/info.tpl',
                       'templates/block.tpl',
                       'img/rss_sm.png',
                       'conf/config.php'
                       );
            podcasterUpdateFiles($files, $content);

            $content[] = '1.0.4 changes
----------------
+ Updated documentation
+ Got channel rss caching working (needs updated /core/class/cache.php)
+ Added empty message and create link, in empty channel list
+ Fixed default for approved flag for new episode by unrestricted users
+ Added podcaster block and related settings
+ Made RSS cache timeout configurable in settings tab
+ Added an info tab and a few notes on media files and iTunes categories
+ General code tidying and review to prepare for release

</pre>';


        case version_compare($currentVersion, '1.0.5', '<'):
            $result = Core\DB::importFile(PHPWS_SOURCE_DIR . 'mod/podcaster/boost/sql_update_105.sql');
            if (Core\Error::isError($result)) {
                Core\Error::log($result);
                $content[] = '+ Unable to import new iTunes categories table.';
                return false;
            } else {
                $content[] = '<pre>';

                $files = array('templates/edit_channel.tpl',
                           'templates/info.tpl'
                           );
                podcasterUpdateFiles($files, $content);

                $content[] = '1.0.5 changes
----------------
+ Added database table and class for iTunes categories
+ Improved iTunes categories functions in channel and form class
+ YOU MUST RESET ITUNES CATEGORIES IF UPGRADING

</pre>';
            }


        case version_compare($currentVersion, '1.1.0', '<'):
            $result = Core\DB::importFile(PHPWS_SOURCE_DIR . 'mod/podcaster/boost/sql_update_110.sql');
            if (Core\Error::isError($result)) {
                Core\Error::log($result);
                $content[] = '+ Unable to upgrade the channel table.';
                return false;
            } else {
                $content[] = '<pre>';
                $content[] = '--- Upgraded channel table.';

                $files = array('templates/edit_channel.tpl',
                           'templates/edit_episode.tpl',
                           'templates/view_channel.tpl',
                           'templates/view_episode.tpl',
                           'templates/list_channel.tpl',
                           'templates/list_episode.tpl',
                           'templates/list_episode_channel.tpl',
                           'templates/view_rss.tpl',
                           'templates/info.tpl'
                           );
                podcasterUpdateFiles($files, $content);
                Core\Core::initModClass('filecabinet', 'Cabinet.php');
                if (Cabinet::convertImagesToFileAssoc('podcaster_channel', 'image_id')) {
                    $content[] = '--- Converted channel images to new File Cabinet format.';
                } else {
                    $content[] = '--- Could not convert channel images to new File Cabinet format.</pre>';
                    return false;
                }
                if (Cabinet::convertMediaToFileAssoc('podcaster_episode', 'media_id')) {
                    $content[] = '--- Converted episode media to new File Cabinet format.';
                } else {
                    $content[] = '--- Could not convert episode media to new File Cabinet format.</pre>';
                    return false;
                }

                $content[] = '
1.1.0 changes
----------------
+ Added support for File Cabinet 2.0 features
+ Added support for document types to episodes
+ Improved the use of phpws key on channels and episodes, 
  allows for phpws categorization, etc
+ Some minor tweaks to view channel and episode templates
+ Added search to list and archive views
+ Resolved some validation issues with rss feed
+ Added support for Search module
+ Fixed a bug in episode delete
+ Fixed a bug in channel delete
+ Implemented short urls for episodes (if mod_rewrite is true)
+ Implimented Core\Text::rewriteLink() for episode view links
  now that it handles multiple vars

</pre>';
            }


        case version_compare($currentVersion, '1.2.0', '<'):
            $content[] = '<pre>';

            /* remove the old init file */
            $initfile = PHPWS_SOURCE_DIR . 'mod/podcaster/inc/init.php';
            if (is_file($initfile)) {
                if (!@unlink($initfile)) {
                    $content[] = 'FAILED TO DELETE mod/podcaster/inc/init.php
YOU MUST REMOVE THIS FILE YOURSELF
';
                } else {
                    $content[] = '- Removed mod/podcaster/inc/init.php
It has been replaced with mod/podcaster/inc/runtime.php
';
                }
            }

            /* update files */
            $files = array('templates/info.tpl'
            );
            podcasterUpdateFiles($files, $content);

            /* update channel keys */
            $error = false;
            Core\Core::initModClass('podcaster', 'PCR_Channel.php');
            $db = new Core\DB('podcaster_channel');
            $channels = $db->getObjects('Podcaster_Channel');
            if (Core\Error::isError($channels)) {
                Core\Error::log($channels);
                $error = true;
            }
            foreach ($channels as $channel) {
                $result = $channel->saveKey();
                if (Core\Error::isError($result)) {
                    Core\Error::log($result);
                    $error = true;
                }
            }
            if ($error) {
                $content[] = '- There was a problem updating your Channel keys
Please save each channel to force an update of the key file.';
            } else {
                $content[] = '- Channel keys successfully updated';
            }

            /* update episode keys */
            $error = false;
            Core\Core::initModClass('podcaster', 'PCR_Episode.php');
            $db = new Core\DB('podcaster_episode');
            $episodes = $db->getObjects('Podcaster_Episode');
            if (Core\Error::isError($episodes)) {
                Core\Error::log($episodes);
                $error = true;
            }
            foreach ($episodes as $episode) {
                $result = $episode->saveKey();
                if (Core\Error::isError($result)) {
                    Core\Error::log($result);
                    $error = true;
                }
            }
            if ($error) {
                $content[] = '- There was a problem updating your Episode keys
Please save each episode to force an update of the key file.';
            } else {
                $content[] = '- Episode keys successfully updated';
            }


            $content[] = '
1.2.0 changes
----------------
+ Moved readme content to README file
+ Made phpws 1.6 compatible
+ Replaced inc/init.php with inc/runtime.php
+ Now restrict read me tab to diety users only

</pre>';



        case version_compare($currentVersion, '1.2.1', '<'):
            $content[] = '<pre>';

            $content[] = '1.2.1 changes
----------------
+ Fixed nasty bug in uninstall (thanks jtickle)
</pre>';


        case version_compare($currentVersion, '1.2.2', '<'):
            $content[] = '<pre>';

            $content[] = '1.2.2 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up
+ Implemented Icon class

</pre>';


    } // end switch
    return true;
}

function podcasterUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'podcaster')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>