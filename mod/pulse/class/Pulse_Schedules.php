<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Pulse_Schedule {
    public $id            = 0;
    /**
     * Name of the module using pulse
     */
    public $module        = null;

    /**
     * Parameters passed to the module's pulse function
     */
    public $parameters    = null;
    
    /**
     * Name of the pulse type. Relates to the file which
     * determines when the next pulse will occur.
     */
    public $pulse_type    = null;

    /**
     * Unix time after which the pulse should occur
     */
    public $pulse_time    = 0;

    /**
     * Records the previous run time
     */
    public $last_run      = 0;

    /**
     * Number of times to repeat the pulse. If zero, then
     * indefinately.
     */
    public $repeats       = 0; 

    /**
     * Number of pulses completed so far
     */
    public $completed     = 0;

    /**
     * If false, do not run the pulse
     */
    public $active        = true;

    public function __construct($id=0)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (PHPWS_Error::logIfError($result) || !$result) {
            $this->id = 0;
        }
    }
    
    public function init()
    {
        $db = new PHPWS_DB('pulse_schedule');
        return $db->loadObject($this);
    }

    public function save()
    {
        $db = new PHPWS_DB('pulse_schedule');
        return $db->saveObject($this);
    }

    /**
     * Calls the module's pulse function with the parameters provided.
     * Then resets the schedule for the next pulse
     */
    public function commit()
    {
        if (!$this->active) {
            //error needed
            return false;
        }

        $filename = sprintf('%smod/%s/inc/pulse.php', PHPWS_SOURCE_DIR, $this->module);
        $func_name = sprintf('%s_pulse', $this->module);
        
        if (!is_file($filename)) {
            //error needed
            return false;
        }

        require_once $filename;

        if (!function_exists($func_name)) {
            //error needed
            return false;
        }

        call_user_func($func_name, $this->parameters);

        $this->last_run = mktime();
        $this->completed++;

        // If repeats is set (greater than zero) and the number of completions
        // is greater than equal to the repeats, then deactivate the schedule.
        if ($this->repeats && $this->completed >= $this->repeats) {
            $this->active = 0;
        }

        $this->resetPulse();
        $this->save();
    }

    public function resetPulse()
    {
        $filename = sprintf('%smod/pulse/inc/pulse_types/%s.php', PHPWS_SOURCE_DIR, $this->pulse_type);

        if (!is_file($filename)) {
            //error needed
            return false;
        }

        $current_pulse_time = $this->pulse_time;

        // Current pulse is carried to the pulse_type. $next_pulse_time is expected in return
        include $filename;

        if (empty($next_pulse_time)) {
            // if pulse time is broken, deactivate schedule.
            $this->active = 0;
            //error needed
            return false;
        }

        $next_pulse_time = (int)$next_pulse_time;
        
        if ($next_pulse_time > $this->pulse_time) {
            $this->pulse_time = $next_pulse_time;
        } else {
            // the pulse time did not advance, disable the schedule
            // error needed
            $this->active = 0;
        }

        // The schedule has gotten behind, set pulse time equal to 
        // current time and reset
        if ($this->pulse_time < $this->last_run) {
            $this->pulse_time = $this->last_run;
            return $this->resetPulse();
        }
        return true;
    }

    public function loadNextPulse()
    {

    }

    public function row_tags()
    {
        $tpl['PULSE_TIME'] = strftime('%d %b, %y - %H:%M', $this->pulse_time);
        if ($this->last_run) {
            $tpl['LAST_RUN'] = strftime('%d %b, %y - %H:%M', $this->last_run);
        } else {
            $tpl['LAST_RUN'] = dgettext('pulse', 'Never run');
        }

        if ($this->active) {
            $tpl['ACTIVE'] = PHPWS_Text::secureLink(dgettext('pulse', 'Yes'), 'pulse', array('aop'=>'disable_schedule'),
                                                    null, dgettext('pulse', 'Click to deactivate'));
        } else {
            $tpl['ACTIVE'] = PHPWS_Text::secureLink(dgettext('pulse', 'Yes'), 'pulse', array('aop'=>'enable_schedule'),
                                                    null, dgettext('pulse', 'Click to activate'));
        }
        return $tpl;
    }

}

?>