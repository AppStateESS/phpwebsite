<?php

class NQ
{
	/**
	 * Get a notification queue from the session.
	 * 
	 * @param String $module The module this queue belongs to
	 * @return NotificationQueue A singleton queue for the specified module
	 */
    protected static function getQueue($module)
    {
        if(!isset($_SESSION['NotificationQueue']))
            $_SESSION['NotificationQueue'] = array();
            
        if(!isset($_SESSION['NotificationQueue'][$module]) ||
           !is_a($_SESSION['NotificationQueue'][$module], 'NotificationQueue')) {
            $_SESSION['NotificationQueue'][$module] = new NotificationQueue($module);
        }
        
        return $_SESSION['NotificationQueue'][$module];
    }
    
    /**
     * Push a Notification on to the module's queue
     * 
     * @param string $module Which queue to add this to 
     * @param Notification $notification The notification to add
     * @return void
     */
    public static function push($module, Notification $notification)
    {
        $queue = self::getQueue($module);
        $queue->push($notification);

    }
    
    /**
     * Pop the top notification from the module's queue
     * @param string $module Which module
     * @return Notification
     */
    public static function pop($module)
    {
    	$queue = self::getQueue($module);
    	return $queue->pop();
    }
    
    /**
     * Get all notifications from the module's queue
     * @param string $module Which module
     * @return array
     */
    public static function popAll($module)
    {
    	$queue = self::getQueue($module);
    	return $queue->popAll();
    }
    
    /**
     * Add a simple notification (SimpleNotification) onto the
     * module's queue
     * 
     * @param string $module Which module
     * @param int $type User-defined type
     * @param string $content The text of the notification
     * @return void
     */
    public static function simple($module, $type, $content)
    {
    	$n = new Notification($type, $content);
    	self::push($module, $n);
    }
}

?>