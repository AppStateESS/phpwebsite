<?php
/**
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if (isset($_REQUEST['command'])) {
    if (!Current_User::allow('menu')) {
        Current_User::disallow();
    } else {
        Menu::admin();
    }
 } elseif (isset($_REQUEST['site_map'])) {
     Menu::siteMap();
 }



?>