<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('PULSE_STATUS_SCHEDULED', 0);
define('PULSE_STATUS_RUNNING', 1);
define('PULSE_STATUS_SUCCESS', 2);
define('PULSE_STATUS_FAILURE' ,3);

abstract class ScheduledPulse
{
    var $id;
    var $name;
    var $execute_at;
    var $module;
    var $class_file;
    var $class;
    var $began_execution;
    var $finished_execution
    var $status = PULSE_STATUS_SCHEDULED;

    public function __construct($id = NULL)
    {
        if(!is_null($id)) {
            $this->id = $id;
            $this->load();
        }
    }

    public abstract function execute();

    protected function makeClone()
    {
        $sp = clone($this);
        $sp->id = 0;
        $sp->status = PULSE_STATUS_SCHEDULED;
        $sp->began_execution = NULL;
        $sp->finished_execution = NULL;
        return $sp;
    }

    protected function newFromNow($seconds)
    {
        $sp = $this->makeClone();
        $sp->execute_at = time() + $seconds;
        $sp->save();
    }

    protected function newFromSupposedExecute($seconds)
    {
        $sp = $this->makeClone();
        $sp->execute_at = $this->execute_at + $seconds;
        $sp->save();
    }

    protected function newExecuteImmediately()
    {
        $sp = $this->makeClone();
        $sp->execute_at = 0;
        $sp->save();
    }

    // TODO: Other utility methods

    public static function getInstance(array $r)
    {
        PHPWS_Core::initModClass($r['module'], $r['class_file']);
        $sp = new $r['class']();
        PHPWS_Core::plugObject($sp, $r);
        return $sp;
    }

    public function load()
    {
        $db = new PHPWS_DB('pulse_schedule');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }
        return TRUE;
    }

    public function save()
    {
        $db = new PHPWS_DB('pulse_schedule');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }
        return TRUE;
    }
}

?>
