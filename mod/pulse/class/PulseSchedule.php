<?php

namespace pulse;

require_once PHPWS_SOURCE_DIR . 'mod/pulse/inc/defines.php';

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PulseSchedule extends \Resource
{
    protected $id;

    /**
     * Time the procedure started.
     * @var integer
     */
    protected $execute_time;

    /**
     * Name of process
     * @var string
     */
    protected $name;

    /**
     * Interval length in minutes
     * @var string
     */
    protected $interim;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $required_file;

    /**
     * @var string
     */
    protected $class_name;

    /**
     * @var string
     */
    protected $class_method;

    /**
     * Time after which the process should begin
     * @var integer
     */
    protected $start_time;

    /**
     * Timestamp of process completion
     * @var integer
     */
    protected $end_time;

    /**
     * Current status of process
     * @var integer
     */
    protected $status;

    /**
     * True if schedule is to proceed. False to temporarily disable.
     * @var boolean
     */
    protected $active;

    /**
     * ID hash for prevention of execution.
     * @var string
     */
    protected $hash;

    /**
     * Table the schedule is saved to.
     * @var string
     */
    protected $table = 'pulse_schedule';

    public function __construct()
    {
        $this->active = new \Variable\Bool(true, 'active');
        $this->class_method = new \Variable\Alphanumeric(null, 'class_method');
        $this->class_name = new \Variable\Alphanumeric(null, 'class_name');
        $this->end_time = new \Variable\Integer(0, 'end_time');
        $this->hash = new \Variable\Hash(null, 'hash');
        $this->hash->allowNull(true);
        $this->interim = new \Variable\Integer(60, 'interim');
        $this->module = new \Variable\Alphanumeric(null, 'module');
        $this->name = new \Variable\Alphanumeric(null, 'name');
        $this->required_file = new \Variable\File(null, 'required_file');
        $this->start_time = new \Variable\Integer(0, 'start_time');
        $this->execute_time = new \Variable\Integer(0, 'execute_time');
        $this->status = new \Variable\Integer(PULSE_STATUS_AWAKE, 'status');
        parent::__construct();
    }

    public function getActive()
    {
        return $this->active->get();
    }

    public function getClassMethod()
    {
        return $this->class_method->get();
    }

    public function getClassName()
    {
        return $this->class_name->get();
    }

    public function getEndTime($format = null)
    {
        if (empty($format)) {
            return $this->end_time->get();
        } else {
            return strftime($format, $this->end_time->get());
        }
    }

    public function getExecuteTime($format = null)
    {
        if (empty($format)) {
            return $this->execute_time->get();
        } else {
            return strftime($format, $this->execute_time->get());
        }
    }

    public function getHash()
    {
        return $this->hash->get();
    }

    public function getInterim()
    {
        return $this->interim->get();
    }

    public function getModule()
    {
        return $this->module->get();
    }

    public function getName()
    {
        return $this->name->get();
    }

    public function getRequiredFile()
    {
        return $this->required_file->get();
    }

    public function getStartTime($format = null)
    {
        if (empty($format)) {
            return $this->start_time->get();
        } else {
            return strftime($format, $this->start_time->get());
        }
    }
    
    public function getStatus()
    {
        return $this->status->get();
    }

    public function setActive($active)
    {
        $this->active->set((bool) $active);
    }

    public function putToSleep()
    {
        $this->setStatus(PULSE_STATUS_ASLEEP);
    }

    public function wakeUp()
    {
        $this->setStatus(PULSE_STATUS_AWAKE);
    }

    public function hold()
    {
        $this->setStatus(PULSE_STATUS_HOLDING);
    }

    public function processing()
    {
        $this->setStatus(PULSE_STATUS_PROCESSING);
    }

    public function setClassMethod($class_method)
    {
        $this->class_method->set($class_method);
    }

    public function setClassName($class_name)
    {
        $this->class_name->set($class_name);
    }

    /**
     * Event completion timestamp
     * @param integer $end_time
     */
    protected function setEndTime($end_time)
    {
        $this->end_time->set($end_time);
    }

    protected function setExecuteTime($execute_time)
    {
        $this->execute_time->set($execute_time);
    }

    public function buildHash()
    {
        $prehash = sha1($this->getName() . $this->getClassName() . $this->getClassMethod() . time());
        $this->hash->set($prehash);
    }

    public function setHash($hash)
    {
        $this->hash->set($hash);
    }

    /**
     * Number of minutes between each scheduled operation
     * @param integer $interim
     */
    public function setInterim($interim)
    {
        $this->interim->set($interim);
    }

    public function setModule($module)
    {
        $this->module->set($module);
    }

    public function setName($name)
    {
        $this->name->set($name);
    }

    public function setRequiredFile($file)
    {
        $this->required_file->set($file);
    }

    /**
     * When the next scheduled event will begin. 
     * 
     * @param integer $start_time
     */
    public function setStartTime($start_time)
    {
        $this->start_time->set($start_time);
    }

    public function setStatus($status)
    {
        $this->status->set($status);
    }

    public function stampExecute()
    {
        $this->setExecuteTime(time());
    }

    public function stampEnd()
    {
        $this->setEndTime(time());
    }

    public function stampStart()
    {
        $this->setStartTime(time());
    }

    /**
     * Returns the number of minutes it took to complete an event. This method
     * must be run prior to the start_time reset.
     * 
     * @return integer
     * @throws \Exception
     */
    public function getProcessLength()
    {
        $length = $this->getEndTime() - $this->getStartTime();
        if ($length < 0) {
            throw new \Exception('Start time was greater than end time.');
        }
        return $length;
    }

}
