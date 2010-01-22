<?php

  /**
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   * @version $Id: unregister.php 5472 2007-12-11 16:13:40Z jtickle $
   */

function link_unregister($module, &$content)
{
    PHPWS_Core::initModClass('link', 'LinkController.php');
    $content[] = dgettext('link', 'Removing module\'s links.');
    if(LinkController::unregisterModule($module)) {
        $content[] = dgettext('link', 'Links (if any) removed successfully');
    } else {
        $content[] = dgettext('link', 'An error occurrred when trying to remove links.');
    }

    return TRUE;
}

?>
