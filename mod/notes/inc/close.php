<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (Current_User::isLogged()) {
    \core\Core::initModClass('notes', 'My_Page.php');

    Notes_My_Page::showUnread();

    $key = \core\Key::getCurrent(false);
    if ($key) {
        Notes_My_Page::miniAdminLink($key);
        Notes_My_Page::showAssociations($key);
    }
}

?>