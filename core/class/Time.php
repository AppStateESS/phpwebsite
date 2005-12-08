<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class PHPWS_Time {

    /**
     * Returns the UTC Unix time based on the server's tz
     * settings
     */
    function getUTCTime()
    {
        return PHPWS_Time::convertUTCTime(mktime(), (int)date('O') / 100, TRUE);
    }


    function getServerTZ()
    {
        static $server_tz = NULL;

        if (!isset($server_tz)) {
            if (defined('SERVER_TIME_ZONE')) {
                $tz = SERVER_TIME_ZONE;

                if (!defined('SERVER_USE_DST') || !SERVER_USE_DST) {
                    $dst = 0;
                } else {
                    $dst = date('I');
                }

            } else {
                $tz = (int)date('O') / 100;
                $dst = 0;
            }

            $server_tz = $tz + $dst;
        }
        
        return $server_tz;
    }

    function mkservertime()
    {
        if (!defined('SERVER_TIME_ZONE')) {
            return mktime();
        } else {
            return PHPWS_Time::getServerTime(PHPWS_Time::getUTCTime());
        }
    }

    /**
     * Get user's timezone or the server time zone if none is
     * set
     */
    function getUserTZ()
    {
        $user_tz = PHPWS_Cookie::read('user_tz');

        if (!isset($user_tz)) {
            return PHPWS_Time::getServerTZ();
        } else {
            $user_dst = PHPWS_Cookie::read('user_dst');
            if (!isset($user_dst)) {
                return $user_tz;
            } else {
                return $user_tz + date('I');
            }
        }
    }


    /**
     * Returns the Unix time of the server when passed
     * the UTC time
     */
    function getServerTime($utc_time)
    {
        return PHPWS_Time::convertUTCTime($utc_time, PHPWS_Time::getServerTZ());
    }


    /**
     * Returns the Unix time of the current user if their
     * timezone cookie is set. Otherwise, returns server time.
     */
    function getUserTime($utc_time)
    {
        $user_tz = PHPWS_Time::getUserTZ();
        return PHPWS_Time::convertUTCTime($utc_time, $user_tz);
    }

    function convertUTCTime($time, $tz, $negative=FALSE)
    {
        if ($negative) {
            $tz *= -1;
        }
        return $time + ($tz * 3600);
    }

    function convertServerTime($time)
    {
        return PHPWS_Time::convertUTCTime($time, PHPWS_Time::getServerTZ(), TRUE);
    }

    function convertUserTime($time)
    {
        return PHPWS_Time::convertUTCTime($time, PHPWS_Time::getUserTZ(), TRUE);
    }

    function getTZList()
    {
        $file = PHPWS_Core::getConfigFile('core', 'timezone.php');
        include $file;
        return $timezones;
    }


}

?>