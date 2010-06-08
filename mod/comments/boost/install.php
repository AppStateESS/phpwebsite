<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function comments_install(&$content)
{
    \core\Core::initModClass('comments', 'Rank.php');
    $rank = new Comment_Rank;
    $rank->group_name = 'All members';
    $rank->save();

    \core\Settings::set('comments', 'default_rank', $rank->id);
    \core\Settings::save('comments');
    return true;
}
?>