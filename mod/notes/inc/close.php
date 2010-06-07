<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (Current_User::isLogged()) {
    Core\Core::initModClass('notes', 'My_Page.php');

    Notes_My_Page::showUnread();

    $key = Key::getCurrent(false);
    if ($key) {
        Notes_My_Page::miniAdminLink($key);
        Notes_My_Page::showAssociations($key);
    }
}

?>