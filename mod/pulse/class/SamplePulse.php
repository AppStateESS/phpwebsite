<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SamplePulse extends ScheduledPulse
{
    public function __construct($id = NULL)
    {
        $this->module = 'pulse';
        $this->class_file = 'SamplePulse.php';
        $this->class = 'SamplePulse';

        parent::__construct($id);
    }

    public function execute()
    {
        echo "SamplePulse, here.  We are go for execute.\n";
        return TRUE;
    }
}

?>
