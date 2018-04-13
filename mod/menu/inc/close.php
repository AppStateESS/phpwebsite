<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}

if (!Menu::$disableMenu) {
    if (PHPWS_Settings::get('menu', 'display_type') == 1) {
        Menu::categoryView();
    } elseif (PHPWS_Settings::get('menu', 'display_type') == 2) {
        Menu::categoryView(true);
    } else {
        Menu::show();
        Menu::showPinned();
    }
}
unset($GLOBALS['MENU_LINKS']);
Menu::miniadmin();
