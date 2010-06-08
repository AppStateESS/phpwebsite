<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::initModClass('search', 'User.php');

Search_User::searchBox();

if (isset($_SESSION['Search_Admin'])) {
    \core\Core::initModClass('search', 'Admin.php');
    Search_Admin::miniAdmin();
}

?>