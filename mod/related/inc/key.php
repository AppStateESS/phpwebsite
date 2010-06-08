<?php

/**
 * unregisters deleted keys from related
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function related_unregister_key(Key $key)
{
    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    $db = new \core\DB('related_main');
    $db->addWhere('key_id', (int)$key->id);
    $db->addColumn('id');
    $result = $db->select('col');

    if (empty($result)) {
        return true;
    }

    $db2 = new \core\DB('related_friends');
    $db2->addWhere('source_id', $result);
    $db2->addWhere('friend_id', $result, '=', 'or');
    $db2->delete();

    $db->reset();
    $db->addWhere('id', $result);
    $db->delete();

    return true;
}

?>