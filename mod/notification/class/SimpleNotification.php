<?php

/**
 * phpWebSite Simple Notification.
 * 
 * Stores an integer type and string content.
 */

PHPWS_Core::initModClass('notification', 'Notification.php');

class SimpleNotification extends Notification
{
	protected var $type;
	protected var $content;
	
	public function __construct($type, $content)
	{
		parent::__construct();
		$this->type = $type;
		$this->content = $content;
	}
	
	public function toString()
	{
		return $this->content;
	}
}

?>