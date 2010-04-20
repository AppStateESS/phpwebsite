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

        case version_compare($currentVersion, '0.6.1', '<'):
            $content[] = '<pre>';
    
            $files = array('templates/edit_item.tpl', 
                            'templates/set_status.tpl', 
                            'templates/edit_settings.tpl'
            );
            vshopUpdateFiles($files, $content);
            
            $content[] = '0.6.1 changes
----------------
+ added is_array checks to links() array_merge in dept and item class
+ added currency and symbol to checkout and confirmation screens
+ added ability to change an item\'s dept
+ added ability to set minimum shipping charge
+ added ability to have free shipping on orders over $xx.xx
+ added ability to change order status from within list view
+ when saving an item, succes now returns with admin menu (thanks wendall)
+ fixed setting links var in navLinks function (thanks wendall)
+ fixed bug preventing add to cart when inventory was not being used
  and qty in stock is 0 (thanks wendall)
+ improved some error checking in post item function (thanks wendall)

</pre>';


        case version_compare($currentVersion, '0.6.2', '<'):
            $content[] = '<pre>';
    
            $content[] = '0.6.2 changes
----------------
+ Updated for phpws Core 2.0.0
+ PHP strict fixes
+ Some code tidy up


</pre>';



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