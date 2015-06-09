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
            $schedule = PulseFactory::pullReadyScheduleByHash($schedule_hash);
            if (empty($schedule)) {
                throw new Exception\PulseException('Schedule hash not found: ' . $schedule_hash);
            } else {
                $schedules[] = $schedule;
            }
        } elseif ($request->isVar('schedule_module')) {
            if ($request->isVar('name')) {
                $schedule_name = $request->getVar('name');
            } else {
                $schedule_name = null;
            }
            $schedule_module = $request->getVar('schedule_module');
            $schedules = PulseFactory::pullReadySchedulesByModule($schedule_module, $schedule_name);
        } elseif ($request->isVar('name')) {
            $schedule_name = $request->getVar('name');
            $schedules = PulseFactory::pullReadySchedulesByName($schedule_name);
        } else {
            $schedules = PulseFactory::pullReadySchedules();
        }

        if (empty($schedules)) {
            exit("No schedules run.\n");
        }

        $completed = PulseFactory::walkSchedules($schedules);
        if (!empty($completed)) {
            foreach ($completed as $sch_id) {
                echo "Schedule #$sch_id completed.\n";
            }
        }

        exit;
    }

}
