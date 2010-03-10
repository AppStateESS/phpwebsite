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

function sitemap_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '0.5.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl',
                           'templates/map_settings.tpl'
                           );
                           sitemapUpdateFiles($files, $content);

                           $content[] = '0.5.0 changes
----------------
+ Separated getMenuItems to its own function so I could..
+ Added ability to get other non-menu keyed items into sitemap
+ Added option to include file cabinet keyed items yes/no
+ A bit of code tidy up
+ Added english lang file

</pre>';


        case version_compare($currentVersion, '0.6.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_settings.tpl',
                           'templates/map_settings.tpl'
                           );
                           sitemapUpdateFiles($files, $content);

                           $content[] = '0.6.0 changes
----------------
+ Added ability to set default exclude list for keyed items
+ Added ability to customize exclude list for manual sitemaps
+ A bit more code tidy up
+ Updated english lang file

</pre>';

        case version_compare($currentVersion, '0.6.1', '<'):
            $content[] = '<pre>0.6.1 changes
----------------
+ translation typo fixed
</pre>';

    } // end switch
    return true;
}

function sitemapUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'sitemap')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>