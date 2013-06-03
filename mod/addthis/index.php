<?php
/**
 * AddThis - phpwebsite module which provides social media sharing buttons.
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
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

// Include some things we're probably going to always need
PHPWS_Core::initModClass('addthis', 'Settings.php');
PHPWS_Core::initModClass('addthis', 'SettingsView.php');


if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'SaveSettings'){
    // save the settings
    $settings = addthis\Settings::getInstance();
    $settingList = $settings->getAll();

    $inputSettings = $_POST['enabled_check'];

    foreach($settingList as $key=>$value){
        if(isset($inputSettings[$key])){
            $settings->set($key, 1);
        }else{
            $settings->set($key, 0);
        }
    }

    // redirect to the 'show settings' page, with a success message
    header('HTTP/1.1 303 See Other');
    header("Location: index.php?module=addthis&action=ShowSettings");
    exit;

}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'ShowSettings') {
    // Create the settings view
    $settingsView = new addthis\SettingsView(addthis\Settings::getInstance());
    \Layout::add($settingsView->show());
}

?>
