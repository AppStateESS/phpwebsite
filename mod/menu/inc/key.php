<?php

/**
 * unregisters deleted keys from menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function menu_unregister_key(Key $key)
{
    PHPWS_Core::initModClass('menu', 'Menu_Link.php');

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

    return true;
}

?>