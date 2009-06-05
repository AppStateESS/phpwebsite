<?php
/**
    * vlist - phpwebsite module
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
    * @author Verdon Vaillancourt <verdonv at users dot sourceforge dot net>
*/

function vlist_update(&$content, $currentVersion)
{
//    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>';
    
            $files = array('templates/block.tpl', 
//                            'templates/set_status.tpl', 
                            'templates/edit_settings.tpl'
            );
            vlistUpdateFiles($files, $content);
            
            $content[] = '1.0.1 changes
----------------
+ Added setting for default sort order
+ Added setting for default sort order
+ Fixed false error when submiting with no custom fields defined
+ Removed option to enable text types in advanced search as
  it isn\'t necessary, and was misleading
+ Tweaked the date format in lists and views
+ Replaced a couple hard-coded Groups label with the one in settings

</pre>';




    } // end switch
    return true;
}

function vlistUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'vlist')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>