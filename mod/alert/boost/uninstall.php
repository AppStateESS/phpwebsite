<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function alert_uninstall(&$content)
{
    Core\DB::dropTable('alert_participant');
    Core\DB::dropTable('alert_prt_to_type');
    Core\DB::dropTable('alert_contact');
    Core\DB::dropTable('alert_item');
    Core\DB::dropTable('alert_type');
    return true;
}
?>