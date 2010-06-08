<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function calendar_uninstall(&$content)
{
    \core\Core::initModClass('calendar', 'Schedule.php');
    // Need functions to remove old event tables
    $db = new \core\DB('calendar_schedule');
    $schedules = $db->getObjects('Calendar_Schedule');

    if (core\Error::isError($schedules)) {
        return $schedules;
    } elseif (empty($schedules)) {
        $result = \core\DB::dropTable('calendar_schedule');
        if (core\Error::isError($result)) {
            return $result;
        }

        $result = \core\DB::dropTable('calendar_notice');
        if (core\Error::isError($result)) {
            return $result;
        }

        $result = \core\DB::dropTable('calendar_suggestions');
        if (core\Error::isError($result)) {
            return $result;
        }

        return true;
    }

    $error = false;

    foreach ($schedules as $sch) {
        $result = $sch->delete();
        if (core\Error::isError($result)) {
            \core\Error::log($result);
            $error = true;
        }
    }

    $result = \core\DB::dropTable('calendar_schedule');
    if (core\Error::isError($result)) {
        return $result;
    }

    $result = \core\DB::dropTable('calendar_notice');
    if (core\Error::isError($result)) {
        return $result;
    }

    $result = \core\DB::dropTable('calendar_suggestions');
    if (core\Error::isError($result)) {
        return $result;
    }

    if (core\DB::isTable('converted')) {
        $db2 = new \core\DB('converted');
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