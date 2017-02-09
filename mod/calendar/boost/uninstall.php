<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function calendar_uninstall(&$content)
{
    \phpws\PHPWS_Core::initModClass('calendar', 'Schedule.php');
    // Need functions to remove old event tables
    $db = new PHPWS_DB('calendar_schedule');
    $schedules = $db->getObjects('Calendar_Schedule');

    if (PHPWS_Error::isError($schedules)) {
        return $schedules;
    } elseif (empty($schedules)) {
        $result = PHPWS_DB::dropTable('calendar_schedule');
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $result = PHPWS_DB::dropTable('calendar_notice');
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $result = PHPWS_DB::dropTable('calendar_suggestions');
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        return true;
    }

    $error = false;

    foreach ($schedules as $sch) {
        $result = $sch->delete();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $error = true;
        }
    }

    $result = PHPWS_DB::dropTable('calendar_schedule');
    if (PHPWS_Error::isError($result)) {
        return $result;
    }

    $result = PHPWS_DB::dropTable('calendar_notice');
    if (PHPWS_Error::isError($result)) {
        return $result;
    }

    $result = PHPWS_DB::dropTable('calendar_suggestions');
    if (PHPWS_Error::isError($result)) {
        return $result;
    }

    if (PHPWS_DB::isTable('converted')) {
        $db2 = new PHPWS_DB('converted');
        $db2->addWhere('convert_name', array('schedule', 'calendar'));
        $db2->delete();
        $content[] = 'Removed convert flag.';
    }

    if (!$error) {
        $content[] = 'Calendar tables removed.';
    } else {
        $content[] = 'Some errors occurred when uninstalling Calendar.';
    }
    return true;
}
