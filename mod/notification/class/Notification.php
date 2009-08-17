<?php

/**
 * phpWebSite Notification.
 * 
 * Abstract class for the Notification system.
 * 
 * Extend this class to store more or different information.
 */

abstract class Notification
{
	protected $timestamp;
	
	public function __construct()
	{
		$this->timestamp = mktime();
	}
	
	public abstract function toString();
}

?>