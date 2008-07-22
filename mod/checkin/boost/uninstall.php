<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function checkin_uninstall(&$content)
{
    PHPWS_DB::dropTable('checkin_staff');
    PHPWS_DB::dropTable('checkin_reasons');
    PHPWS_DB::dropTable('checkin_visitor');
    PHPWS_DB::dropTable('checkin_rtos');
    return true;
}


?>