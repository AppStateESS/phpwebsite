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
//define('IGNORE_BEFORE', '20050601');

PHPWS_Core::initModClass('calendar', 'Schedule.php');
PHPWS_Core::initModClass('calendar', 'Event.php');

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
        $content[] = _('You may convert to different ways.');
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
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

    if (!isset($_SESSION['schedule_id'])) {
        return false;
    } else {
        $schedule = & new Calendar_Schedule((int)$_SESSION['schedule_id']);
    }

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldEvent) {
            $result = convertEvent($oldEvent, $schedule);
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
    $schedule = & new Calendar_Schedule;
    $schedule->title = 'Conversion';
    $schedule->summary = 'Events pulled from 0.10.x calendar module.';
    $schedule->public = true;
    $result = $schedule->save();
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return false;
    }

    $_SESSION['schedule_id'] = $schedule->id;

    return true;
}


function convertEvent($event, &$schedule)
{
    $new_event = & new Calendar_Event;
    $new_event->_schedule = $schedule;

    $new_event->summary = $event['title'];
    $new_event->setDescription($event['description']);

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
        $start_time = strtotime(sprintf('%sT%s', $event['startDate'], $event['startTime']));
        $end_time = strtotime(sprintf('%sT%s', $event['endDate'], '0000')) + 86400;
        break;

    case 'interval':
    default:
        $start_time = strtotime(sprintf('%sT%s', $event['startDate'], $event['startTime']));
        $end_time = strtotime(sprintf('%sT%s', $event['endDate'], $event['endTime']));
        break;
    }

    $new_event->start_time = $start_time;
    $new_event->end_time = $end_time;
    $new_event->all_day = $all_day;

    return $new_event->save();
}


function createSeqTable()
{
    $schedule = & new Calendar_Schedule((int)$_SESSION['schedule_id']);
    $table = $schedule->getEventTable();
    $db = new PHPWS_DB($table);
    return $db->updateSequenceTable();
}

?>