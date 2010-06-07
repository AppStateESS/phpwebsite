<?php
/**
 * Removes the comment user from the database when
 * a user is deleted
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function comments_remove_user($user_id)
{
    Core\Core::initModClass('comments', 'Comment_User.php');
    $comment_user = new Comment_User($user_id);
    if (!$comment_user->user_id) {
        return;
    }

    return $comment_user->kill();
}

?>