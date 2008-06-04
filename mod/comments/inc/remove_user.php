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
    PHPWS_Core::initModClass('comments', 'Comment_User.php');
    $comment_user = new Comment_User($user_id);
    return $comment_user->delete();
}

?>