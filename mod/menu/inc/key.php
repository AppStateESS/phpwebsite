<?php

/**
 * unregisters deleted keys from menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function menu_unregister_key(\Canopy\Key $key)
{
    \phpws\PHPWS_Core::initModClass('menu', 'Menu_Link.php');

    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $db = new PHPWS_DB('menu_links');
    $db->addWhere('key_id', $key->id);
    $result = $db->delete();

    if (PHPWS_Error::isError($result)) {
        PHPWS_Error::log($result);
    }

    $db2 = new PHPWS_DB('menu_assoc');
    $db2->addWhere('key_id', $key->id);
    $result = $db2->delete();

    if (PHPWS_Error::isError($result)) {
        PHPWS_Error::log($result);
    }
    
    $db3 = new PHPWS_DB('menus');
    $db3->addWhere('assoc_key', $key->id);
    $db3->addValue('assoc_key', 0);
    $db3->addValue('assoc_url', null);
    $db3->update();

    return true;
}
