<?php

/**
 * Tracker Factory
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class TrackerFactory
{
    public static function getActive()
    {
        $db = self::initDb();
        $db->addWhere('active', 1);
        return self::runQuery($db);
    }

    public static function getById($id)
    {
        $db = self::initDb();
        $db->addWhere('id', $id);

        $trackers = self::runQuery($db);
        return $trackers[0];
    }

    public static function newByType($type)
    {
        PHPWS_Core::initModClass('analytics', "trackers/$type.php");
        return new $type();
    }

    public static function getAll()
    {
        $db = self::initDb();
        return self::runQuery($db);
    }

    public static function getAvailableClasses()
    {
        $tracker_files = scandir(PHPWS_SOURCE_DIR . 'mod/analytics/class/trackers');
        $trackers = array();

        foreach($tracker_files as $file)
        {
            if(substr($file, -4) != '.php') continue;
            $trackers[] = substr($file, 0, -4);
        }

        return $trackers;
    }

    protected static function initDb()
    {
        return new PHPWS_DB('analytics_tracker');
    }

    protected static function runQuery($db)
    {
        $result = $db->select();
        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        $trackers = array();
        foreach($result as $tracker) {
            $found = PHPWS_Core::initModClass('analytics', "trackers/{$tracker['type']}.php");
            if(!$found) {
                continue;
            }
            $t = new $tracker['type']();
            PHPWS_Core::plugObject($t, $tracker);
            $trackers[] = $t;
        }

        return $trackers;
    }
}

?>
