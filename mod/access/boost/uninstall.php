<?php

/**
 * Uninstall file for access
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function access_uninstall(&$content)
{
    Core\DB::dropTable('access_shortcuts');
    Core\DB::dropTable('access_allow_deny');
    $content[] = dgettext('access', 'Access tables removed.');
    return TRUE;
}

?>