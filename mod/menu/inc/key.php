<?php

/**
 * unregisters deleted keys from menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function menu_unregister(&$key)
{

    PHPWS_Core::initModClass('menu', 'Menu_Link.php');

    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $link = &  new Menu_Link;

    $db = & new PHPWS_DB('menu_links');
    $db->addWhere('key_id', $key->id);
    $db->loadObject($link);
    $db->reset();
    $link->_db = &$db;
    $result = $link->delete(TRUE);
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

}

?>