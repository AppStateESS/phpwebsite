<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function comments_install(&$content)
{
    Core\Core::initModClass('comments', 'Rank.php');
    $rank = new Comment_Rank;
    $rank->group_name = 'All members';
    $rank->save();

    Core\Settings::set('comments', 'default_rank', $rank->id);
    Core\Settings::save('comments');
    return true;
}
?>