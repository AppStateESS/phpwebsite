<?php

namespace pulse;

/**
 * Description
 * @author Matthew McNaney <mcnaneym at appstate dot edu>
 */
class PulseController
{

    public static function runSchedules(\Request $request)
    {
        if ($request->isVar('hash')) {
            $schedule_hash = $request->getVar('hash');
            if (preg_match('/\W/', $schedule_hash)) {
                throw new Exception\PulseException('Improper schedule hash');
            }
            $schedule = PulseFactory::pullScheduleByHash($schedule_hash);
            if (empty($schedule)) {
                throw new Exception\PulseException('Schedule not found: ' . $schedule_hash);
            } else {
                $schedules[] = $schedule;
            }
        } elseif ($request->isVar('name')) {
            $schedule_name = $request->getVar('name');
            $schedule = PulseFactory::pullScheduleByName($schedule_name);
            if (empty($schedule)) {
                throw new Exception\PulseException('Schedule not found: ' . $schedule_name);
            } else {
                $schedules[] = $schedule;
            }
        } else {
            $schedules = PulseFactory::pullReadySchedules();
        }

        if (empty($schedules)) {
            exit('No schedules run.');
        }

        $completed = PulseFactory::walkSchedules($schedules);
        if (!empty($completed)) {
            echo '<pre>';
            foreach ($completed as $sch_id) {
                echo "Schedule #$sch_id completed.\n";
            }
            echo '</pre>';
        }

        exit;
    }
}
