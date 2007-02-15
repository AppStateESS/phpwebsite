<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_unregister($module, &$content)
{
    translate('comments');
    PHPWS_Core::initModClass('comments', 'Comments.php');
    $content[] = _('Removing module\'s comments.');
    if (Comments::unregister($module)) {
        $content[] = _('Comments (if any) removed successfully');
    } else {
        $content[] = _('An error occurred when trying to remove comments.');
    }
    translate();
    return TRUE;
}

?>