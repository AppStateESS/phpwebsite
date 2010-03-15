<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}

Menu::show();
Menu::showPinned();
Menu::miniadmin();

?>