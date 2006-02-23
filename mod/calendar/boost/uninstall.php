<?php

  /**
   * Uninstall file for calendar
   */

function calendar_uninstall(&$content)
{
    PHPWS_DB::dropTable('calendar_events');
    PHPWS_DB::dropTable('calendar_schedule');
    PHPWS_DB::dropTable('calendar_notice');
    PHPWS_DB::dropTable('calendar_schedule_to_event');
    $content[] = _('Calendar tables removed.');
    return TRUE;
}


?>