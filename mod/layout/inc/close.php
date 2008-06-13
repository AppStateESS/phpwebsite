<?php

/**
 * Crutch display of old modules
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (Current_User::allow('layout')) {
    Layout::miniLinks();
 }

Layout::keyDescriptions();
Layout::showKeyStyle();
if (defined('LAYOUT_CHECK_COOKIE') && LAYOUT_CHECK_COOKIE) {
    check_cookie();
}
echo Layout::display();
?>