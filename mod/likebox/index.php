<?php
/**
 * AddThis - phpwebsite module which provides social media sharing buttons.
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Appalachian State University 2013
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

// Include some things we're probably going to always need
PHPWS_Core::initModClass('likebox', 'LikeboxSettings.php');
PHPWS_Core::initModClass('likebox', 'SettingsView.php');


if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'SaveSettings'){
    // save the settings
    $settings = LikeboxSettings::getInstance();

    $settings->set('enabled', (int)isset($_POST['enabled']));
    $settings->set('small_header', (int)isset($_POST['small_header']));
    $settings->set('hide_cover', (int)isset($_POST['hide_cover']));
    $settings->set('show_posts', (int)isset($_POST['show_posts']));
    $settings->set('show_faces', (int)isset($_POST['show_faces']));

    // Save the text fields
    $settings->set('fb_url', filter_input(INPUT_POST, 'fb_url', FILTER_SANITIZE_SPECIAL_CHARS));
    $settings->set('width', filter_input(INPUT_POST, 'width', FILTER_SANITIZE_NUMBER_INT));
    $settings->set('height', filter_input(INPUT_POST, 'height', FILTER_SANITIZE_NUMBER_INT));

    // redirect to the 'show settings' page, with a success message
    header('HTTP/1.1 303 See Other');
    header("Location: index.php?module=likebox&action=ShowSettings");
    exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'ShowSettings') {
    // Create the settings view
    $settingsView = new SettingsView(LikeboxSettings::getInstance());
    Layout::add($settingsView->show());
}

?>
