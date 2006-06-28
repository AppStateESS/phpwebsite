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

    function getTimeArray($time=0)
    {
        if (!$time) {
            $time = PHPWS_Time::mkservertime();
        }

        $aTime['m'] = (int)strftime('%m', $time);
        $aTime['d'] = (int)strftime('%e', $time);
        $aTime['y'] = (int)strftime('%Y', $time);
        $aTime['h'] = (int)strftime('%k', $time);
        $aTime['i'] = (int)strftime('%M', $time);
        $aTime['u'] = $time;

        return $aTime;
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


    /**
     * Relative time calculation
     *
     * @author René C. Kiesler <http://www.kiesler.at/>
     * @modified Matthew McNaney <mcnaney at gmail dot com>
     * @date   2006-02-03
     *
     */
    function relativeTime($timestamp, $format='%c') {
        translate('core');
        $rel   = time() - $timestamp;
        $mins  = floor($rel / 60);
        $hours = floor($mins / 60);
        $days  = floor($hours / 24);
        $weeks = floor($days/ 7);
      
      
        if    ($mins < 2) {
            return _('a heartbeat ago');
        }
        elseif($mins < 60) {
            return sprintf(_('%s mins ago'), $mins);
        }
        elseif($hours==1) {
            return _('1 hour ago');
        }
        elseif($hours< 24) {
            return sprintf(_('%s hours ago'), $hours);
        }
        elseif($days ==1) {
            return _('1 day ago');
        }
        elseif($days < 7) {
            return sprintf(_('%s days ago'), $days);
        }
        elseif($weeks==1) {
            return _('1 week ago');
        }
        elseif($weeks< 4) {
            return sprintf(_('%s weeks ago'), $weeks);
        }
        else {
            return strftime($format, $timestamp);
        }
        
    }
}

?>