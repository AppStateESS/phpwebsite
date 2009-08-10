<?php

  /**
   * The PHPWS_Time class is mainly for the parsing of timestamps
   * into and out of UTC time.
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class PHPWS_Time {

    public function convertServerTime($time)
    {
        $admin_offset = 3600 * PHPWS_Time::getServerTZ();
        $server_offset = date('Z');
        $time += $server_offset;
        $time -= $admin_offset;
        return $time;
    }

    public function convertUserTime($time)
    {
        $user_offset = 3600 * PHPWS_Time::getUserTZ();
        $server_offset = date('Z');
        $time += $server_offset;
        $time -= $user_offset;
        return $time;
    }

    /**
     * Returns a time in iCal format
     */
    public function getDTTime($time=0, $mode='utc')
    {
        if (!$time) {
            $time = mktime();
        }
        switch ($mode) {
        case 'user':
            $new_time = PHPWS_Time::getUserTime($time);
            $tz = PHPWS_Time::getUserTZ();
            break;

        case 'server':
            $new_time = PHPWS_Time::getServerTime($time);
            $tz = PHPWS_Time::getServerTZ();
            break;

        case 'all_day':
        case 'utc':
            $new_time = &$time;
            $tz = 0;
            break;
        }


        if ($mode == 'all_day') {
            return gmstrftime('%Y%m%d', $new_time);
        }

        $dttime = gmstrftime('%Y%m%dT%H%M00', $new_time);
        if ($tz != 0) {
            if ($tz > 0) {
                $sign = '+';
            } else {
                $sign = '-';
            }

            $tz = sqrt($tz*$tz) * 1000;

            if ($tz < 10) {
                $tz = '0' . $tz;
            }
            $tzadd = $sign . $tz;

        } else {
            $tzadd = 'Z';
        }
        $dttime .= $tzadd;

        return $dttime;
    }


    public function getServerTime($time=0)
    {
        if (!$time) {
            $time = mktime();
        }

        $admin_offset = 3600 * PHPWS_Time::getServerTZ();
        $server_offset = date('Z');
        $time -= $server_offset;
        $time += $admin_offset;
        return $time;
    }

    public function getUserTime($time=0)
    {
        if (!$time) {
            $time = mktime();
        }

        $user_offset = 3600 * PHPWS_Time::getUserTZ();
        $server_offset = date('Z');
        $time -= $server_offset;
        $time += $user_offset;
        return $time;
    }


    public function getServerTZ()
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


    /**
     * Get user's timezone or the server time zone if none is
     * set
     */
    public function getUserTZ()
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


    public function getTimeArray($time=0)
    {
        if (!$time) {
            $time = PHPWS_Time::getServerTime();
        }

        $aTime['m'] = (int)strftime('%m', $time);
        $aTime['d'] = (int)strftime('%e', $time);
        $aTime['y'] = (int)strftime('%Y', $time);
        $aTime['h'] = (int)strftime('%k', $time);
        $aTime['i'] = (int)strftime('%M', $time);
        $aTime['u'] = $time;

        return $aTime;
    }


    public function getTZList()
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
    public function relativeTime($timestamp, $format='%c')
    {
        $timestamp = intval($timestamp);
        $rel   = time() - $timestamp;
        $mins  = floor($rel / 60);
        $hours = floor($mins / 60);
        $days  = floor($hours / 24);
        $weeks = floor($days/ 7);

        if ($mins < 2) {
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