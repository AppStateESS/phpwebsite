<?php

/**
 * Uninstall file for webpage
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function webpage_uninstall(&$content)
{
    Core\DB::dropTable('webpage_volume');
    Core\DB::dropTable('webpage_page');
    Core\DB::dropTable('webpage_featured');
    $content[] = dgettext('webpage', 'Web Page tables removed.');
    return TRUE;
}

?>