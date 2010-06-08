<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (core\Core::atHome() || isset($GLOBALS['comments_viewed'])) {
    $show_number = \core\Settings::get('comments', 'recent_comments');
    if ($show_number) {
        \core\Core::initModClass('comments', 'Comments.php');
        Comments::showRecentComments($show_number);
    }
}

?>