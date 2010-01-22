<?php

  /**
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   * @version $Id: unregister.php 5472 2007-12-11 16:13:40Z jtickle $
   */

function poll_unregister($module, &$content)
{
    PHPWS_Core::initModClass('poll', 'PollController.php');
    $content[] = dgettext('poll', 'Removing module\'s polls.');
    if(PollController::unregisterModule($module)) {
        $content[] = dgettext('poll', 'Polls (if any) removed successfully');
    } else {
        $content[] = dgettext('poll', 'An error occurrred when trying to remove polls.');
    }

    return TRUE;
}

function ratings_unregister($module, &$content)
{
    PHPWS_Core::initModClass('ratings', 'Ratings.php');
    $content[] = dgettext('ratings', 'Removing module\'s ratings.');
    if (Ratings::unregister($module)) {
        $content[] = dgettext('ratings', 'Ratings (if any) removed successfully');
    } else {
        $content[] = dgettext('ratings', 'An error occurred when trying to remove ratings.');
    }
    
    return TRUE;
}

?>
