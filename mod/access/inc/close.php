<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (Current_User::allow('access') && PHPWS_Settings::get('access', 'shortcuts_enabled')) {
    $key = Key::getCurrent();
    if (!empty($key) && !$key->isDummy()) {
        PHPWS_Core::initModClass('access', 'Access.php');
        Access::shortcut($key);
    }
 }

?>