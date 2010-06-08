<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function alert_uninstall(&$content)
{
    \core\DB::dropTable('alert_participant');
    \core\DB::dropTable('alert_prt_to_type');
    \core\DB::dropTable('alert_contact');
    \core\DB::dropTable('alert_item');
    \core\DB::dropTable('alert_type');
    return true;
}
?>