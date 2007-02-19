<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function menu_install(&$content)
{
    translate('menu');
    PHPWS_Core::initModClass('menu', 'Menu_Item.php');
    $menu = new Menu_Item;
    $menu->title = _('Main menu');
    $menu->template = 'basic.tpl';
    $menu->pin_all = 1;
    $result = $menu->save();
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        translate();
        return false;
    } else {
        $content[] = _('Default menu created successfully.');
        translate();
        return true;
    }

}

?>