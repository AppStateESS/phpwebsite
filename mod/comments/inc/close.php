<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (Core\Core::atHome() || isset($GLOBALS['comments_viewed'])) {
    $show_number = PHPWS_Settings::get('comments', 'recent_comments');
    if ($show_number) {
        Core\Core::initModClass('comments', 'Comments.php');
        Comments::showRecentComments($show_number);
    }
}

?>