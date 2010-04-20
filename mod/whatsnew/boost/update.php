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

function whatsnew_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '0.1.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/block.tpl');
            whatsnewUpdateFiles($files, $content);

            $content[] = '0.1.0 changes
----------------
+ Added caching to the whatsnew list

</pre>';

        case version_compare($currentVersion, '0.2.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl');
            whatsnewUpdateFiles($files, $content);

            $content[] = '0.2.0 changes
----------------
+ Added sidebox title to settings
+ Implimented text parsing to title and text settings
+ Added English language file

</pre>';

        case version_compare($currentVersion, '0.3.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl');
            whatsnewUpdateFiles($files, $content);

            $content[] = '0.3.0 changes
----------------
+ Added cache_reset to post_settings function (thanks Greg)
+ Added link and function to manually flush cache at will

</pre>';

        case version_compare($currentVersion, '1.0.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl', 'templates/block.tpl');
            whatsnewUpdateFiles($files, $content);

            $content[] = '1.0.0 changes
----------------
+ Added option to display item summaries (thanks obones)
+ Added option to display item update dates (thanks obones)
+ Bumped version to 1.0.0 as mod is being added to core distro
  and it feels at that stage.
+ Moved Read Me information to the README file in docs/

</pre>';

        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl', 'templates/block.tpl');
            whatsnewUpdateFiles($files, $content);

            $content[] = '1.0.2 changes
----------------
+ Added option to display item source modules (thanks obones)

</pre>';


        case version_compare($currentVersion, '1.0.3', '<'):
            $content[] = '<pre>';

            $content[] = '1.0.3 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up

</pre>';



    } // end switch
    return true;
}

function whatsnewUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'whatsnew')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>