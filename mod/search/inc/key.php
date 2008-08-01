<?php

/**
 * unregisters deleted keys from search
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function search_unregister_key(PHPWS_Key $key)
{
    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $db = new PHPWS_DB('search');
    $db->addWhere('key_id', (int)$key->id);
    return $db->delete();
}

?>