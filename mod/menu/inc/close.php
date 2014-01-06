<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}

if (PHPWS_Settings::get('menu', 'display_type')) {
    Menu::categoryView();
} else {
    Menu::show();
    Menu::showPinned();
}
unset($GLOBALS['MENU_LINKS']);
Menu::miniadmin();
?>