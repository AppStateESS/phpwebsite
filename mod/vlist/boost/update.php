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
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
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


        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>';

            $db = new PHPWS_DB('vlist_element');
            if (!$db->isTableColumn('private')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('private', 'smallint NOT NULL default 0'))) {
                    $content[] = '--- Could not create column private on vlist_element table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created private column on vlist_element table.';
                }
            }

            $files = array( 'templates/edit_checkbox.tpl',
                            'templates/edit_dropbox.tpl', 
                            'templates/edit_multiselect.tpl', 
                            'templates/edit_radiobutton.tpl', 
                            'templates/edit_textarea.tpl', 
                            'templates/edit_textfield.tpl', 
                            'templates/list_elements.tpl', 
                            'templates/list_listings.tpl', 
                            'img/locked.png', 
                            'img/unlocked.png', 
                            'templates/edit_settings.tpl'
                            );
            vlistUpdateFiles($files, $content);

            $content[] = '1.0.2 changes
----------------
+ Added setting for internal only fields
+ Added setting for user profile privacy
+ Tweaked edit settings screen a little
+ Tweaked some permissions
+ Fixed bug in posting GPS field type (thanks trf000)
+ Added Google Map special field type

</pre>';



        case version_compare($currentVersion, '1.0.3', '<'):
            $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/vlist/boost/sql_update_103.sql');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = '+ Unable to upgrade the database according to mod/vlist/boost/sql_update_103.sql.';
                return false;
            } else {
                $content[] = '<pre>';

                $files = array('templates/list_listings.tpl',
                                'img/approved.png', 
                                'img/unapproved.png', 
                                'templates/edit_listing.tpl'
                                );
                vlistUpdateFiles($files, $content);

                $content[] = '1.0.3 changes
----------------
+ Fixed bug I introduced in 1.0.2 that prevented editing values
  in custom field setup
+ Added ID to item list views
+ Changed active var to approved in list class
+ Added new active var to list class
+ Users may now distiguish between active and approved


</pre>';
            }


        case version_compare($currentVersion, '1.0.5', '<'):
            $content[] = '<pre>';

            $content[] = '1.0.4 changes
----------------
+ Fixed search in list bug
+ Fixed images and files from showing, if setting is changed to no
  after having been yes and files/images attached

1.0.5 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up


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