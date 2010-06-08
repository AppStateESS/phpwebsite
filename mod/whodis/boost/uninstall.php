<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function whodis_uninstall()
{
    Core\DB::dropTable('whodis');
    Core\DB::dropTable('whodis_filters');
    return true;
}

?>
