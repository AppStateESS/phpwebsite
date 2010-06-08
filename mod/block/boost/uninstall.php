<?php

/**
 * Uninstall file for block
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function block_uninstall(&$content)
{
    Core\DB::dropTable('block');
    Core\DB::dropTable('block_pinned');
    $content[] = dgettext('block', 'Block tables removed.');
    return true;
}


?>
