<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!Current_User::authorized('branch')) {
    Current_User::disallow();
}

PHPWS_Core::initModClass('branch', 'Branch_Admin.php');
$branch_admin = new Branch_Admin;
$branch_admin->main();

?>