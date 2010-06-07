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

    PHPWS_Settings::set('comments', 'default_rank', $rank->id);
    PHPWS_Settings::save('comments');
    return true;
}
?>