<?php

  /**
   * Uninstall file for blog
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function rideboard_install(&$content)
{
    if (PHPWS_Core::initModClass('menu', 'Menu.php')) {
        Menu::pinLink('Ride Board', 'index.php?module=rideboard');
        Menu::enableAdminMode();
    }
    return true;
}

?>
