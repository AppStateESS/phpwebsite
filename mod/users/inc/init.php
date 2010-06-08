<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::configRequireOnce('users', 'config.php', TRUE);
require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';
core\Core::configRequireOnce('users', 'tags.php');
core\Core::initModClass('users', 'Current_User.php');

?>
