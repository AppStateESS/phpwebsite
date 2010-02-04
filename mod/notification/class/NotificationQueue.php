<?php

class NotificationQueue
{
	var $queue;
	var $module;
	
	public function __construct($module)
	{
		$this->queue  = array();
		$this->module = $module;
	}
	
	public function push(Notification $notification)
	{
		array_push($this->queue, $notification);
	}
	
	public function pop()
	{
		return array_shift($this->queue);
	}
	
	public function popAll()
	{
		$q = $this->queue;
		$this->queue = array();
		return $q;
	}

    public function isEmpty()
    {
        return empty($this->queue);
    }
}
?>
