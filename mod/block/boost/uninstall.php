<?php

/**
 * Uninstall file for block
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function block_uninstall(&$content)
{
    PHPWS_DB::dropTable('block');
    PHPWS_DB::dropTable('block_pinned');
    $content[] = dgettext('block', 'Block tables removed.');
    return true;
}


?>
