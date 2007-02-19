<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (MOD_REWRITE_ENABLED && Current_User::allow('access') && 
    PHPWS_Settings::get('access', 'shortcuts_enabled') && PHPWS_Settings::get('access', 'rewrite_engine')) {
    $key = Key::getCurrent();
    if (!empty($key) && !$key->isDummy()) {
        PHPWS_Core::initModClass('access', 'Access.php');
        translate('access');
        Access::shortcut($key);
        translate();
    }
 }

?>