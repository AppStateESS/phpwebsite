<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::configRequireOnce('users', 'config.php', TRUE);
require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';
PHPWS_Core::configRequireOnce('users', 'tags.php');
PHPWS_Core::initModClass('users', 'Current_User.php');

?>
