<?php

/**
 * unregisters deleted keys from comments
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function comments_unregister_key(Key $key)
{
    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

    \core\Core::initModClass('comments', 'Comment_Thread.php');

    $thread = new Comment_Thread;

    $db = new \core\DB('comments_threads');
    $db->addWhere('key_id', $key->id);
    $result = $db->loadObject($thread);

    if (core\Error::isError($result)) {
        return $result;
    } elseif (empty($result)) {
        return TRUE;
    }

    return $thread->delete();
}

?>