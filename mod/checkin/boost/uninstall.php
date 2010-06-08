<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function checkin_uninstall(&$content)
{
    Core\DB::dropTable('checkin_staff');
    Core\DB::dropTable('checkin_reasons');
    Core\DB::dropTable('checkin_visitor');
    Core\DB::dropTable('checkin_rtos');
    return true;
}


?>