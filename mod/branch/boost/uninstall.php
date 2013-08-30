<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function branch_uninstall(&$content)
{
    PHPWS_DB::dropTable('branch_sites');
    return TRUE;
}


?>