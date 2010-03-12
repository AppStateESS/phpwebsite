<?php

/**
 * Calendar conversion file
 *
 * Transfers calendar events
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


// number of events to convert at a time. lower this number if you are having
// memory or timeout errors
define('EVENT_BATCH', 10);

// Must be in YYYYMMDD format.
// If you want to convert all your events, leave this line commented out.
// define('IGNORE_BEFORE', '20050601');

PHPWS_Core::initModClass('calendar', 'Schedule.php');
PHPWS_Core::initModClass('calendar', 'Event.php');
PHPWS_Core::initModClass('calendar', 'Admin.php');

function convert()
{
    if (Convert::isConverted('calendar')) {
        return _('Calendar has already been converted.');
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('calendar', $mod_list)) {
        return _('Calendar is not installed.');
    }

    if (!Convert::isConverted('schedule')) {
        if (createSchedule()) {
            Convert::addConvert('schedule');
            $content[] = 'Conversion schedule created.';
        } else {
            return 'An error occurred when trying to create a conversion schedule. Check your error logs.';
        }
    }

    if (!isset($_REQUEST['mode'])) {
        $content[] = _('You may convert two different ways.');
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=calendar&mode=manual',
        _('Manual mode requires you to click through the conversion process.'));
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=calendar&mode=auto',
        _('Automatic mode converts the data without your interaction.'));

        $content[] = ' ';
        $content[] = _('If you encounter problems, you should use manual mode.');
        $content[] = _('Conversion will begin as soon as you make your choice.');

        return implode('<br />', $content);
    } else {
        if ($_REQUEST['mode'] == 'auto') {
            $show_wait = TRUE;
        } else {
            $show_wait = FALSE;
        }

        $db = Convert::getSourceDB('mod_calendar_events');
        if (!$db) {
            $content[] = _('Calendar is not installed in other database.');
            return implode('<br />', $content);
        }
        if (defined('IGNORE_BEFORE')) {
            $db->addWhere('startDate', IGNORE_BEFORE, '>=');
        }

        $batch = & new Batches('convert_events');

        $total_entries = $db->count();
        if ($total_entries < 1) {
            return _('No events to convert.');
        }

        $batch->setTotalItems($total_entries);
        $batch->setBatchSet(EVENT_BATCH);

        if (isset($_REQUEST['reset_batch'])) {
            $batch->clear();
        }

        if (!$batch->load()) {
            $content[] = _('Batch previously run.');
        } else {
            $result = runBatch($db, $batch);
            if (!$result) {
                $content[] = _('Failed to run batch. Check your session settings.');
            } elseif (is_array($result)) {
                foreach ($result as $error) {
                    $content[] = $error;
                }
            }
        }

        $percent = $batch->percentDone();
        $content[] = Convert::getGraph($percent, $show_wait);
        $batch->completeBatch();

        if (!$batch->isFinished()) {
            if ($_REQUEST['mode'] == 'manual') {
                $content[] =  $batch->continueLink();
            } else {
                Convert::forward($batch->getAddress());
            }
        } else {
            createSeqTable();
            $batch->clear();
            Convert::addConvert('calendar');
            $content[] =  _('All done!');
            $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
            unset($_SESSION['schedule_id']);
        }

        return implode('<br />', $content);
    }
}

function runBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();

    $db->disconnect();
    Convert::siteDB();

    if (!isset($_SESSION['schedule_id'])) {
        return false;
    } else {
        $schedule = & new Calendar_Schedule((int)$_SESSION['schedule_id']);
    }

    $admin = & new Calendar_Admin;

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldEvent) {
            $result = convertEvent($oldEvent, $schedule, $admin);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $errors[] = 'Problem importing: ' . $oldEvent['title'];
            }
        }
    }

    if (isset($errors)) {
        return $errors;
    } else {
        return TRUE;
    }
}

function createSchedule()
{
    $schedule = new Calendar_Schedule;
    $schedule->title = _('Conversion');
    $schedule->summary = _('Events pulled from 0.10.x calendar module.');
    $schedule->public = true;
    $result = $schedule->save();
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return false;
    }

    PHPWS_Settings::set('calendar', 'public_schedule', $schedule->id);
    PHPWS_Settings::save('calendar');
    $_SESSION['schedule_id'] = $schedule->id;
    return true;
}


function convertEvent($event, &$schedule, &$admin)
{
    $new_event = & new Calendar_Event;
    $new_event->_schedule = $schedule;

    $new_event->summary = PHPWS_Text::breaker(utf8_encode($event['title']));

    if (!empty($event['image'])) {
        $image = explode(':', $event['image']);
        $prefix = 'images/calendar/';
        $image_tag = sprintf('<img src="%s" width="%s" height="%s" />', $prefix . $image[0], $image[1], $image[2]);
        if (!empty($event['description'])) {
            $event['description'] = sprintf('<table width="100%%" cellpadding="5"><tr><td>%s</td><td>%s</td></tr></table>', utf8_encode($event['description']), $image_tag);
        } else {
            $event['description'] = $image_tag;
        }
    }

    $new_event->setDescription($event['description']);

    $start_temp = (int)$event['startTime'];
    $end_temp = (int)$event['endTime'];

    if ($start_temp >= 0) {
        $hour = floor((int)$start_temp/100);
        $minute = (int)$start_temp % 100;

        if ($minute < 10) {
            $minute = '0' . $minute;
        }

        if ($hour < 10) {
            $hour = '0' . $hour;
        }

        $event['startTime'] = sprintf('%s:%s', $hour, $minute);
    }

    if ($end_temp >= 0) {
        $hour = floor((int)$end_temp/100);
        $minute = (int)$end_temp % 100;

        if ($minute < 10) {
            $minute = '0' . $minute;
        }

        if ($hour < 10) {
            $hour = '0' . $hour;
        }

        $event['endTime'] = sprintf('%s:%s', $hour, $minute);
    }



    $all_day = 0;
    switch ($event['eventType']) {
        case 'allday':
            $start_time = strtotime(sprintf('%sT%s', $event['startDate'], '0000'));
            $end_time = strtotime(sprintf('%sT%s', $event['endDate'], '2359'));
            $all_day = 1;
            break;

        case 'deadline':
            $start_time = strtotime(sprintf('%sT%s', $event['startDate'], '0000'));
            $end_time = strtotime(sprintf('%sT%s', $event['endDate'], $event['endTime']));
            break;

        case 'start':
            if ($event['startTime'] > 1) {
                $start_time = strtotime(sprintf('%sT%s', $event['startDate'], $event['startTime']));
                $end_time = strtotime(sprintf('%sT%s', $event['endDate'], '0000')) + 86400;
            } else {
                $start_time = strtotime(sprintf('%sT%s', $event['endDate'], $event['endTime']));
                $end_time = strtotime(sprintf('%sT%s', $event['endDate'], '0000')) + 86400;
            }
            break;

        case 'interval':
        default:
            $start_time = strtotime(sprintf('%sT%s', $event['startDate'], $event['startTime']));
            $end_time = strtotime(sprintf('%sT%s', $event['endDate'], $event['endTime']));
            break;
    }

    if ($event['endRepeat']) {
        setRepeat($new_event, $event);
    }


    $new_event->start_time = $start_time;
    $new_event->end_time = $end_time;
    $new_event->all_day = $all_day;
    $result = $new_event->save();

    if ($new_event->end_repeat) {
        $admin->saveRepeat($new_event);
    }
}

function setRepeat(&$new_event, &$event)
{
    // set to one second before midnight
    $new_event->end_repeat = strtotime($event['endRepeat']) + 86399;

    switch ($event['repeatMode']) {
        case 'weekly':
            $weekdays = explode(':', $event['repeatWeekdays']);
            $sunday = array_shift($weekdays);
            $weekdays[] = $sunday;
            $new_event->repeat_type = 'weekly:' . implode(';', $weekdays);
            break;
        default:
            $new_event->repeat_type = $event['repeatMode'];
    }

}


function createSeqTable()
{
    $schedule = new Calendar_Schedule((int)$_SESSION['schedule_id']);
    $table = $schedule->getEventTable();
    $db = new PHPWS_DB($table);
    return $db->updateSequenceTable();
}

?>