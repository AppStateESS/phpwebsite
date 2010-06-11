<?php

PHPWS_Core::initModClass('notification', 'SimpleNotification.php');
PHPWS_Core::initModClass('notification', 'NotificationQueue.php');
PHPWS_Core::initModClass('notification', 'Notification.php');

class NQ
{
    private static $queues;

    public static function init()
    {
        if(isset($_SESSION['NotificationQueue'])) {
            self::$queues = unserialize($_SESSION['NotificationQueue']);
        }
    }

    public static function close()
    {
        if(count(self::$queues) > 0) {
            $_SESSION['NotificationQueue'] = serialize(self::$queues);
        } else {
            if(isset($_SESSION['NotificationQueue'])) {
                unset($_SESSION['NotificationQueue']);
            }
        }
    }

    /**
     * Get a notification queue from the session.
     *
     * @param String $module The module this queue belongs to
     * @return NotificationQueue A singleton queue for the specified module
     */
    protected static function getQueue($module)
    {
        if(!isset(self::$queues[$module]) ||
    	   !is_a(self::$queues[$module], 'NotificationQueue')) {
    	       self::$queues[$module] = new NotificationQueue($module);
    	   }

        return self::$queues[$module];
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
        $n = new SimpleNotification($type, $content);
        self::push($module, $n);
    }

    /**
     * Determines if a queue is empty.
     *
     * @param string $module Which module
     * @return boolean True if empty
     */
    public static function isEmpty($module)
    {
        $queue = self::getQueue($module);
        return $queue->isEmpty();
    }
}

?>
