<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


PHPWS_Core::configRequireOnce('users', 'config.php', TRUE);
PHPWS_Core::configRequireOnce('users', 'errorDefines.php', TRUE);
PHPWS_Core::configRequireOnce('users', 'tags.php');

PHPWS_Core::initModClass('users', 'Users.php');
PHPWS_Core::initModClass('users', 'Current_User.php');
?>
