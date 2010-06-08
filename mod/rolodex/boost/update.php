<?php
/**
 * rolodex - phpwebsite module
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

function rolodex_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {


        case version_compare($currentVersion, '0.7.0', '<'):
            $result = \core\DB::importFile(PHPWS_SOURCE_DIR . 'mod/rolodex/boost/sql_update_070.sql');
            if (core\Error::isError($result)) {
                \core\Error::log($result);
                $content[] = '+ Unable to add the location and feature tables.';
                return false;
            } else {
                $content[] = '<pre>';

                $files = array('templates/edit_location.tpl',
                           'templates/edit_feature.tpl',
                           'templates/edit_member.tpl',
                           'templates/list_location.tpl',
                           'templates/list_feature.tpl',
                           'templates/list_category.tpl',
                           'templates/list_member.tpl',
                           'templates/view_member.tpl',
                           'templates/adv_search_form.tpl',
                           'templates/alpha_click.tpl',
                           'templates/edit_settings.tpl'
                           );
                rolodexUpdateFiles($files, $content);

                $content[] = '0.7.0 changes
----------------
+ Add support for Location(s) and Feature(s)
+ Added a bunch of new views to support those
+ Improved Category views
+ Improved image handling
+ Removed avatar support (its in My Page > Comments anyways)
+ Improved CSV export
+ A few security tweaks
+ Added en_US language file
+ Added inc/remove_user.php for new user mod features
+ Added a couple date format examples to config.php
+ Consolidated various nav links arrays into one function
</pre>';
            }

        case version_compare($currentVersion, '0.8.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/message_member.tpl',
                       'templates/edit_settings.tpl'
                       );
            rolodexUpdateFiles($files, $content);

            $content[] = '0.8.0 changes
----------------
+ Added new setting to use link or form for contact
+ Added memnber contact form
+ Added setting for CAPTCHA on contact form
</pre>';


        case version_compare($currentVersion, '0.9.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/adv_search_form.tpl',
                       'templates/list_member.tpl',
                       'templates/view_member.tpl',
                       'templates/message_member.tpl',
                       'templates/edit_settings.tpl'
                       );
            rolodexUpdateFiles($files, $content);

            $content[] = '0.9.0 changes
----------------
+ Various text improvements throughout
+ Added more details to list views
+ Added new settings for customizing list views
+ Added options for including custom fields in lists
</pre>';


        case version_compare($currentVersion, '1.0.0', '<'):
            \core\Core::initModClass('demographics', 'Demographics.php');
            if (core\Error::logIfError(Demographics::registerDefaultField('evening_phone'))) {
                $content[] = 'Could not register evening_phone column in demographics table.</pre>';
                return false;
            }
            $content[] = '<pre>';

            $files = array('templates/edit_member.tpl',
                       'templates/edit_settings.tpl',
                       'templates/adv_search_form.tpl',
                       'templates/list_member.tpl',
                       'templates/view_member.tpl',
                       'img/view.gif',
                       'templates/block.tpl'
                       );
            rolodexUpdateFiles($files, $content);

            $content[] = '1.0.0 changes
----------------
+ Registered evening_phone field with demographics module
+ Split phone privacy settings into home and business
+ Added thumbnail to side-box view
+ Minor fix to prevent titles for features/locations/categories
  showing up in various places for members who have none
+ Minor fix in reporting success after contact form submit
+ Added some tip text to edit member form
+ Further verbage improvements
+ Added View icon to member viewLink(icon=false)
+ Added option to have admin notification of all member edits
+ Added Settings link to navLinks() if not already an admin mode
+ Consolidated list by location/feature/category views into
  main browse/list function
+ Cleaned out a lot of old code from old view by lists
+ Template tweaks
</pre>';


        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '<pre>';
            $db = new \core\DB('rolodex_member');
            if (!$db->isTableColumn('email_privacy')) {
                if (core\Error::logIfError($db->addTableColumn('email_privacy', 'smallint NOT NULL default 0'))) {
                    $content[] = '--- Could not create column email_privacy on rolodex_member table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created email_privacy column on rolodex_member table.';
                }
            }

            $files = array('templates/edit_member.tpl',
                       'templates/edit_settings.tpl', 
                       'templates/adv_search_form.tpl', 
                       'templates/view_member.tpl', 
                       'templates/list_member.tpl'
                       );
            rolodexUpdateFiles($files, $content);

            $content[] = '1.1.0 changes
----------------
+ Added support for email/contact link privacy at user level 
+ Minor layout tweaks
+ Fixed bug in post settings that wasn\'t clearning custom field 
  labels
+ Added security check so that only users with edit_member
  perms can edit internal custom field data
+ Added Categories to dependency.xml and check file
+ Re-organized some control panel tabs into sub-tabs
+ Added settings to enable or disable categories, locations,
  and features
+ Some tweaking of users permissions and security

</pre>';


        case version_compare($currentVersion, '1.2.0', '<'):
            $content[] = '<pre>';

            $content[] = '1.2.0 changes
----------------
+ Fixed bug where some list parameters were being cleared when 
  a search was not being done, when paging a list of users
+ Fixed a session bug in RDX_Runtime
+ Fixed a few php notices
+ Made Read Me tab restricted to deity users

</pre>';


        case version_compare($currentVersion, '1.3.0', '<'):
            $content[] = '<pre>';

            $initfile = PHPWS_SOURCE_DIR . 'mod/rolodex/inc/init.php';
            if (is_file($initfile)) {
                if (!@unlink($initfile)) {
                    $content[] = 'FAILED TO DELETE mod/rolodex/inc/init.php
YOU MUST REMOVE THIS FILE YOURSELF
';
                } else {
                    $content[] = '- Removed mod/rolodex/inc/init.php
It has been replaced with mod/rolodex/inc/runtime.php
';
                }
            }

            $files = array('templates/message_member.tpl'
            );
            rolodexUpdateFiles($files, $content);

            $content[] = '1.3.0 changes
----------------
+ Converted to phpws 1.6 url rewriting method
+ Removed old mod_rewrite code
+ Begin rewriting to php5 standards
+ Moved readme content to docs/readme
+ Updated CAPTCHA support

</pre>';


        case version_compare($currentVersion, '1.3.1', '<'):
            $content[] = '<pre>';

            $content[] = '1.3.1 changes
----------------
+ Fixed bug in printCVS function
+ Improved privacy in CVS export
+ Changed email hiding to hide from all but admins
+ Fixed a minor display issue with instances of getDisplay_name()
+ Fixed bug in thumbnail linking to 1.6 url rewriting
+ Fixed bug in RDX_Runtime that was usually causing the sideblock 
  to not show for logged in users (thanks trf000)
+ fixed typo in Category form instruction

</pre>';


        case version_compare($currentVersion, '1.3.3', '<'):
            $content[] = '<pre>';

            $content[] = '1.3.2 changes
----------------
+ Reorganized changelog
+ Fixed odd bug that could lead to duplicate tabs when adding the
  first location or feature (thanks majjhima)

1.3.3 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up
+ Implemented Icon class

</pre>';


    } // end switch
    return true;
}

function rolodexUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'rolodex')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>