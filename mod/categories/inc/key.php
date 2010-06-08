<?php

/**
 * unregisters deleted keys from categories
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function categories_unregister_key(&$key)
{
    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $db = new Core\DB('category_items');
    $db->addWhere('key_id', (int)$key->id);
    return $db->delete();
}

?>