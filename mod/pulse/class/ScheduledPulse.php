<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class ScheduledPulse
{
    var $id;
    var $name;
    var $execute_after;
    var $module;
    var $class_file;
    var $class;
    var $execute_time;
    var $success;

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
        return $sp;
    }

    protected function newFromNow($seconds)
    {
        $sp = $this->makeClone();
        $sp->execute_after = time() + $seconds;
        $sp->save();
    }

    protected function newFromSupposedExecute($seconds)
    {
        $sp = $this->makeClone();
        $sp->execute_after = $this->execute_after + $seconds;
        $sp->save();
    }

    protected function newExecuteImmediately()
    {
        $sp = $this->makeClone();
        $sp->execute_after = 0;
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
