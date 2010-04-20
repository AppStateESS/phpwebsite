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

function vpath_update(&$content, $currentVersion)
{
    $home_directory = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {

        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>';

            $files = array( 'templates/edit_settings.tpl');
            vpathUpdateFiles($files, $content);

            $content[] = '
1.0.1 changes
---------------------
+ Added setting to hide path on Home page
+ Added setting to make current location clickable
+ Added a few more comments in functions
</pre>';


        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>';
            $content[] = '
1.0.2 changes
---------------------
+ Fixed bug in buildTrail() function
</pre>';


        case version_compare($currentVersion, '1.0.3', '<'):
            $content[] = '<pre>';

            $files = array( 'templates/edit_settings.tpl',
                        'templates/sub.tpl'
                        );
            vpathUpdateFiles($files, $content);

            $content[] = '
1.0.3 changes
---------------------
+ Added option to display sub menu for current location
</pre>';

        case version_compare($currentVersion, '1.0.4', '<'):
            $content[] = '<pre>';
    
            $files = array( 'templates/edit_settings.tpl', 
                            'templates/path.tpl', 
                            'templates/sub.tpl'
            );
            vpathUpdateFiles($files, $content);
        
            $content[] = '
1.0.4 changes
---------------------
+ Added option to display peer menu for current location
  when no sub/child items exist
+ Minor tweaks to path template
</pre>';
        

        case version_compare($currentVersion, '1.0.5', '<'):
            $content[] = '<pre>';
    
            $content[] = '
1.0.5 changes
---------------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up


</pre>';
        


    } // end switch
    return true;
}


function vpathUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'vpath')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>