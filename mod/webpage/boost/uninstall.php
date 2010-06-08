<?php

/**
 * Uninstall file for webpage
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function webpage_uninstall(&$content)
{
    \core\DB::dropTable('webpage_volume');
    \core\DB::dropTable('webpage_page');
    \core\DB::dropTable('webpage_featured');
    $content[] = dgettext('webpage', 'Web Page tables removed.');
    return TRUE;
}

?>