<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function calendar_uninstall(&$content)
{
    PHPWS_Core::initModClass('calendar', 'Schedule.php');
    // Need functions to remove old event tables
    $db = new PHPWS_DB('calendar_schedule');
    $schedules = $db->getObjects('Calendar_Schedule');

    if (PEAR::isError($schedules)) {
        return $schedules;
    } elseif (empty($schedules)) {
        $result = PHPWS_DB::dropTable('calendar_schedule');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result = PHPWS_DB::dropTable('calendar_notice');
        if (PEAR::isError($result)) {
            return $result;
        }

        $result = PHPWS_DB::dropTable('calendar_suggestions');
        if (PEAR::isError($result)) {
            return $result;
        }

        return true;
    }

    $error = false;

    foreach ($schedules as $sch) {
        $result = $sch->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $error = true;
        }
    }

    $result = PHPWS_DB::dropTable('calendar_schedule');
    if (PEAR::isError($result)) {
        return $result;
    }

    $result = PHPWS_DB::dropTable('calendar_notice');
    if (PEAR::isError($result)) {
        return $result;
    }

    $result = PHPWS_DB::dropTable('calendar_suggestions');
    if (PEAR::isError($result)) {
        return $result;
    }

    if (PHPWS_DB::isTable('converted')) {
        $db2 = new PHPWS_DB('converted');
        $db2->addWhere('convert_name', array('schedule', 'calendar'));
        $db2->delete();
        $content[] = dgettext('calendar', 'Removed convert flag.');
    }

    if (!$error) {
        $content[] = dgettext('calendar', 'Calendar tables removed.');
    } else {
        $content[] = dgettext('calendar', 'Some errors occurred when uninstalling Calendar.');
    }
    return true;
}

?>