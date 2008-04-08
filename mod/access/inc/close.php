<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!isset($_SESSION['Access_Allow_Deny'])) {
    PHPWS_Core::initModClass('access', 'Access.php');
    Access::allowDeny();
}

if (!$_SESSION['Access_Allow_Deny']) {
    PHPWS_Core::initModClass('access', 'Access.php');
    Access::denied();
}


if (MOD_REWRITE_ENABLED && Current_User::allow('access')) {
    $key = Key::getCurrent();
    if (!empty($key) && !$key->isDummy()) {
        PHPWS_Core::initModClass('access', 'Access.php');
        Access::shortcut($key);
    }
 }

?>