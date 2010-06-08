<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function branch_uninstall(&$content)
{
    \core\DB::dropTable('branch_sites');
    \core\DB::dropTable('branch_mod_limit');
    return TRUE;
}


?>