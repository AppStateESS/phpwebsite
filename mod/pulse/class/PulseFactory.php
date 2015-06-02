<?php

namespace pulse;

require_once PHPWS_SOURCE_DIR . 'mod/pulse/inc/defines.php';

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PulseFactory extends \ResourceFactory
{

    public static function getById($id)
    {
        $schedule = new PulseSchedule;
        if (self::loadByID($schedule, $id)) {
            return $schedule;
        } else {
            throw new \Exception('Pulse Schedule not found.');
        }
    }

    /**
     * Save the Pulse Schedule
     * @param \pulse\PulseSchedule $schedule
     */
    public static function save(PulseSchedule $schedule)
    {
        self::checkSaveRequirements($schedule);
        if (!$schedule->isSaved()) {
            self::startTimer($schedule);
        }

        self::saveResource($schedule);
    }

    /**
     * Throws exceptions if all the needed data isn't present.
     * @param \pulse\PulseSchedule $schedule
     * @throws \Exception
     */
    private static function checkSaveRequirements(PulseSchedule $schedule)
    {
        if (empty($schedule->getName())) {
            throw new \Exception('Schedule name may not be empty');
        }
        if (empty($schedule->getRequiredFile())) {
            throw new \Exception('Required file may not be empty');
        }
        if (empty($schedule->getClassName())) {
            throw new \Exception('Class name may not be empty');
        }
        if (empty($schedule->getClassMethod())) {
            throw new \Exception('Class method name may not be empty');
        }
        if (empty($schedule->getInterim())) {
            throw new \Exception('Interim must be greater than zero minutes.');
        }
    }

    private static function startTimer($schedule)
    {
        $start_time = $schedule->getStartTime();
        if (empty($start_time)) {
            $start = time();
            $schedule->setStartTime($start);
        }
    }

    /**
     * Instantiates empty Pulse Schedule
     * @return \pulse\PulseSchedule
     */
    public static function build()
    {
        $schedule = new PulseSchedule;
        return $schedule;
    }

    public static function delete($id)
    {
        $schedule = self::load($id);
        self::deleteResource($schedule);
    }

    public static function pullScheduleByName($name)
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('pulse_schedule');
        $tbl->addFieldConditional('name', $name);
        $tbl->addFieldConditional('active', 1);
        $tbl->addFieldConditional('status', PULSE_STATUS_AWAKE);
        $tbl->addFieldConditional('start_time', time(), '<');
        $row = $db->selectOneRow();
        return $row;
    }

    public static function pullScheduleByHash($hash)
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('pulse_schedule');
        $tbl->addFieldConditional('hash', $hash);
        $tbl->addFieldConditional('active', 1);
        $tbl->addFieldConditional('status', PULSE_STATUS_AWAKE);
        $tbl->addFieldConditional('start_time', time(), '<');
        $row = $db->selectOneRow();
        return $row;
    }

    public static function pullReadySchedules()
    {
        $db = \Database::newDB();
        $tbl = $db->addTable('pulse_schedule');
        $tbl->addFieldConditional('active', 1);
        $tbl->addFieldConditional('status', PULSE_STATUS_AWAKE);
        $tbl->addFieldConditional('start_time', time(), '<');
        $tbl->addFieldConditional('hash', null, 'is');
        $schedules = $db->select();
        return $schedules;
    }

    public static function logError($message)
    {
        $log_filename = 'pulse.log';
        \PHPWS_Core::log('Error: ' . $message, $log_filename);
    }

    public static function walkSchedules(array $schedules)
    {
        $schedules_completed = null;
        $error_occurred = false;
        foreach ($schedules as $job) {
            $schedule = new PulseSchedule;
            $schedule->setVars($job);
            try {
                PulseFactory::executeSchedule($schedule);
                $schedules_completed[] = $schedule->getId();
            } catch (\Exception $e) {
                self::logError($e->getMessage());
                $error_occurred = true;
            }
        }
        if ($error_occurred) {
            throw new Exception\PulseException('One or more errors occurred during schedule execution.');
        } else {
            return $schedules_completed;
        }
    }

    private static function checkIfScheduleisRunable(PulseSchedule $schedule)
    {
        $required_file = $schedule->getRequiredFile();
        $id = $schedule->getId();

        if (!is_file($required_file)) {
            $error = "Schedule #$id could not could not find required file: $required_file";
            throw new \Exception($error);
        }

        $class_name = $schedule->getClassName();

        if (!class_exists($class_name)) {
            $error = "Schedule #$id contained an unknown class name: $class_name";
            throw new \Exception($error);
        }

        $class_method = $schedule->getClassMethod();
        if (!in_array($class_method, get_class_methods($class_name))) {
            $error = "Schedule #$id contained an unknown class method name: $class_method";
            throw new \Exception($error);
        }
    }

    private static function logScheduleCompletion(PulseSchedule $schedule, $result)
    {
        $id = $schedule->getId();
        $execute_time = $schedule->getExecuteTime('%c');
        $end_time = $schedule->getEndTime('%c');

        $log = <<<EOF
Schedule #$id was executed at $execute_time and completed at $end_time.
Returned result: $result
EOF;

        \PHPWS_Core::log($log, 'pulse.log');
    }

    private static function loadNextRun(PulseSchedule $schedule)
    {
        $next_time = time() + ($schedule->getInterim() * 60);
        $schedule->setStartTime($next_time);
    }

    public static function executeSchedule(PulseSchedule $schedule)
    {
        self::checkIfScheduleisRunable($schedule);

        $schedule->stampExecute();
        self::save($schedule);

        $class_name = $schedule->getClassName();
        $class_method = $schedule->getClassMethod();
        $required_file = $schedule->getRequiredFile();

        require_once $required_file;

        // Execution has begun. Set execute time and processing status.
        $schedule->stampExecute();
        $schedule->processing();
        self::save($schedule);

        $result = call_user_func(array($class_name, $class_method));
        
        // Execution is finished. Set end time and return to awake status.
        $schedule->stampEnd();
        $schedule->wakeUp();
        self::loadNextRun($schedule);
        self::save($schedule);
        
        self::logScheduleCompletion($schedule, $result);
    }

    public static function pagerRows($row)
    {
        $row['start_time'] = strftime('%Y%m%d %H:%M', $row['start_time']);
        if (empty($row['end_time'])) {
            $row['end_time'] = 'Not complete';
        } else {
            $row['end_time'] = strftime('%Y%m%d %H:%M', $row['end_time']);
        }

        if (empty($row['execute_time'])) {
            $row['execute_time'] = 'Not yet executed';
        } else {
            $row['execute_time'] = strftime('%Y%m%d %H:%M', $row['execute_time']);
        }

        $interim = '';
        $hours = floor($row['interim'] / 60);
        $minutes = $row['interim'] % 60;
        if (!empty($hours)) {
            $interim .= $hours . ' hrs., ';
        }
        $interim .= $minutes . ' mins.';
        $row['interim'] = $interim;

        $row['runtime'] = gmdate('H:i:s', $row['end_time'] - $row['execute_time']);
        
        switch ($row['status']) {
            case PULSE_STATUS_AWAKE:
                $row['status'] = '<span class="label label-success">Awake</span>';
                break;
            case PULSE_STATUS_ASLEEP:
                $row['status'] = '<span class="label label-warning">Asleep</span>';
                break;
            case PULSE_STATUS_PROCESSING:
                $row['status'] = '<span class="label label-info">Processing...</span>';
                break;
            case PULSE_STATUS_HOLDING:
                $row['status'] = '<span class="label label-danger">Holding</span>';
                break;
        }
        return $row;
    }

}
