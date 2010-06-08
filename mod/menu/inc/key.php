<?php

/**
 * unregisters deleted keys from menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function menu_unregister_key(Key $key)
{
    Core\Core::initModClass('menu', 'Menu_Link.php');

    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $db = new Core\DB('menu_links');
    $db->addWhere('key_id', $key->id);
    $result = $db->delete();

    if (Core\Error::isError($result)) {
        Core\Error::log($result);
    }

    $db2 = new Core\DB('menu_assoc');
    $db2->addWhere('key_id', $key->id);
    $result = $db2->delete();

    if (Core\Error::isError($result)) {
        Core\Error::log($result);
    }

    return true;
}

?>