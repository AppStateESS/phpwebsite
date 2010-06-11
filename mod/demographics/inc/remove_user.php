<?php

/**
 * Removes the demographic user from the database when
 * a user is deleted
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function demographics_remove_user($user_id)
{
    $db = new PHPWS_DB('demographics');
    $db->addWhere('user_id', (int)$user_id);
    return $db->delete();
}

?>