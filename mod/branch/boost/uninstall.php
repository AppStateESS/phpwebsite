<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function branch_uninstall(&$content)
{
    PHPWS_DB::dropTable('branch_sites');
    PHPWS_DB::dropTable('branch_mod_limit');
    return TRUE;
}


?>