<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function whodis_uninstall()
{
    \core\DB::dropTable('whodis');
    \core\DB::dropTable('whodis_filters');
    return true;
}

?>
