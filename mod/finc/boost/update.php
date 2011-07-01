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
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

function finc_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/info.tpl');
            fincUpdateFiles($files, $content);

            $content[] = '1.1.0 changes
----------------
+ Setting up

</pre>';


        case version_compare($currentVersion, '1.1.1', '<'):
            $content[] = '<pre>';

            $content[] = '1.1.1 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up
+ Implemented Icon class
</pre>';


    } // end switch
    return true;
}

function fincUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'finc')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>