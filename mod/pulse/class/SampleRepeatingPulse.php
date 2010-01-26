<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SampleRepeatingPulse extends ScheduledPulse
{
    public function __construct($id = NULL)
    {
        $this->module = 'pulse';
        $this->class_file = 'SampleRepeatingPulse.php';
        $this->class = 'SampleRepeatingPulse';

        parent::__construct($id);
    }

    public function execute()
    {
        $secs = 50;
        $date = date('H:i:s');
        echo "SampleRepeatingPulse.  The time is $date.  I will execute again in $secs seconds.\n";

        $this->newFromNow($secs);

        return TRUE;
    }
}

?>
