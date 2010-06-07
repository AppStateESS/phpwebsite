<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::configRequireOnce('users', 'config.php', TRUE);
require_once PHPWS_SOURCE_DIR . 'mod/users/inc/errorDefines.php';
Core\Core::configRequireOnce('users', 'tags.php');
Core\Core::initModClass('users', 'Current_User.php');

?>
