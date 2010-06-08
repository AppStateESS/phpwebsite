<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function related_uninstall(&$content)
{
    Core\DB::dropTable('related_friends');
    Core\DB::dropTable('related_main');
    $content[] = dgettext('related', 'Related tables removed.');
    return true;
}

?>