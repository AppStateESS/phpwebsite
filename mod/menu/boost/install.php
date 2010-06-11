<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function menu_install(&$content)
{
    PHPWS_Core::initModClass('menu', 'Menu_Item.php');
    $menu = new Menu_Item;
    $menu->title = dgettext('menu', 'Main menu');
    $menu->template = 'basic';
    $menu->pin_all = 1;
    $result = $menu->save();
    if (PHPWS_Error::isError($result)) {
        PHPWS_Error::log($result);
        return false;
    } else {
        $content[] = dgettext('menu', 'Default menu created successfully.');
        return true;
    }
}

?>