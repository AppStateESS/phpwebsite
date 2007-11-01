<?php

  /**
   * Uninstall file for blog
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id: uninstall.php 4583 2007-04-04 19:12:02Z matt $
   */

function rideboard_install(&$content)
{
    if (PHPWS_Core::initModClass('menu', 'Menu.php')) {
        Menu::pinLink('Ride Board', 'index.php?module=rideboard&amp;tab=my_rides');
        Menu::enableAdminMode();
    }
    return true;
}

?>
