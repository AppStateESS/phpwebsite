<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_SESSION['Access_Allow_Deny'])) {
    \core\Core::initModClass('access', 'Access.php');
    Access::allowDeny();
}

if (!$_SESSION['Access_Allow_Deny']) {
    \core\Core::initModClass('access', 'Access.php');
    Access::denied();
}


if (MOD_REWRITE_ENABLED && Current_User::allow('access')) {
    $key = \core\Key::getCurrent();
    if (!empty($key) && !$key->isDummy()) {
        \core\Core::initModClass('access', 'Access.php');
        Access::shortcut($key);
    }
}

?>