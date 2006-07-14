<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function calendar_uninstall(&$content)
{
    // Need functions to remove old event tables
    PHPWS_DB::dropTable('calendar_schedule');
    $content[] = 'Calendar tables removed.';
    return true;
}

?>