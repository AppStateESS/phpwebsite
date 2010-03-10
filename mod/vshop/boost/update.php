<?php
/**
 * vshop - phpwebsite module
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

function vshop_update(&$content, $currentVersion)
{
    //    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($currentVersion) {

        case version_compare($currentVersion, '0.6.0', '<'):
            $content[] = '<pre>';

            $files = array('templates/edit_item.tpl',
                            'templates/set_status.tpl', 
                            'templates/edit_settings.tpl'
                            );
                            vshopUpdateFiles($files, $content);

                            $content[] = '0.6.0 changes
----------------
+ added is_array checks to links() array_merge in dept and item class
+ added currency and symbol to checkout and confirmation screens
+ added ability to change an item\'s dept
+ added ability to set minimum shipping charge
+ added ability to have free shipping on orders over $xx.xx
+ added ability to change order status from within list view

</pre>';


        case version_compare($currentVersion, '0.7.0', '<'):
            $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/vshop/boost/sql_update_070.sql');
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = '+ Unable to add the option sets and values, and attributes tables.';
                return false;
            } else {
                $content[] = '+ added the option sets and values, and attributes tables.';
                $content[] = '<pre>';

                $files = array('templates/list_option_sets.tpl',
                           'templates/list_option_values.tpl',
                           'templates/edit_settings.tpl'
                           );
                           vshopUpdateFiles($files, $content);

                           $content[] = '0.7.0 changes
----------------
+ added support for product attributes

</pre>';
            }



    } // end switch
    return true;
}

function vshopUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'vshop')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>