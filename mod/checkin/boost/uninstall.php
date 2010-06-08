<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function checkin_uninstall(&$content)
{
    \core\DB::dropTable('checkin_staff');
    \core\DB::dropTable('checkin_reasons');
    \core\DB::dropTable('checkin_visitor');
    \core\DB::dropTable('checkin_rtos');
    return true;
}


?>