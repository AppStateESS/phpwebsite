<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PulseController
{
    public function process($params)
    {
        switch($params['action']) {
            case 'Pulse':
                $this->pulse();
                break;
            case 'Admin':
                $this->admin();
                break;
        }
    }

    public function pulse()
    {
        PHPWS_Core::initModClass('pulse', 'ScheduledPulse.php');
        $exec = time();

        $db = new PHPWS_DB('pulse_schedule');
        $db->addWhere('execute_after', $exec, '<');
        $db->addWhere('execute_time', NULL);

        // I feel the need to explain myself here as this seems like this is entirely done wrong.
        // It does an individual select every time so that if a Scheduled Pulse is designed to run
        // every 20 minutes and an hour passes between pulses, it can be up to the developer to decide
        // whether the Pulse shold execute 3 times in that hour, or only once, as I can see cases
        // where either may be desirable.  The real bottleneck in Pulse is the actual execution and
        // since all this is happening in the background, I'm not as worried about inefficient
        // database accesses.
        $execCount = -1;
        while($execCount != 0) {
            $execCount = 0;

            // Get One Result
            $result = $db->select();

            if(PHPWS_Error::logIfError($result)) {
                echo "ERROR IN PULSE\n" . $result->__toString();
                exit();
            }

            foreach($result as $r) {

                // Find the subclass of ScheduledPulse
                $sp = ScheduledPulse::getInstance($r);

                // Try and execute
                try {
                    if($sp->execute() !== TRUE) {
                        $sp->success = false;
                        echo "PULSE RETURNED FALSE\n";
                    } else $sp->success = true;
                } catch(Exception $e) {
                    echo "EXCEPTION EXECUTING PULSE\n" . $e->__toString();
                    $sp->success = false;
                }

                $sp->execute_time = $exec;
                $sp->save();
                $execCount++;
            }
        }

        echo "END OF PULSE\n";
        exit();
    }

    public function admin()
    {
        Layout::add('Have not got around to it yet.');
    }
}

?>
