<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function calendar_uninstall(&$content)
{
    Core\Core::initModClass('calendar', 'Schedule.php');
    // Need functions to remove old event tables
    $db = new Core\DB('calendar_schedule');
    $schedules = $db->getObjects('Calendar_Schedule');

    if (Core\Error::isError($schedules)) {
        return $schedules;
    } elseif (empty($schedules)) {
        $result = Core\DB::dropTable('calendar_schedule');
        if (Core\Error::isError($result)) {
            return $result;
        }

        $result = Core\DB::dropTable('calendar_notice');
        if (Core\Error::isError($result)) {
            return $result;
        }

        $result = Core\DB::dropTable('calendar_suggestions');
        if (Core\Error::isError($result)) {
            return $result;
        }

        return true;
    }

    $error = false;

    foreach ($schedules as $sch) {
        $result = $sch->delete();
        if (Core\Error::isError($result)) {
            Core\Error::log($result);
            $error = true;
        }
    }

    $result = Core\DB::dropTable('calendar_schedule');
    if (Core\Error::isError($result)) {
        return $result;
    }

    $result = Core\DB::dropTable('calendar_notice');
    if (Core\Error::isError($result)) {
        return $result;
    }

    $result = Core\DB::dropTable('calendar_suggestions');
    if (Core\Error::isError($result)) {
        return $result;
    }

    if (Core\DB::isTable('converted')) {
        $db2 = new Core\DB('converted');
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