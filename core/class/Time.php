<?php

class PHPWS_Time {

    function getUTCTime($time=0)
    {
        return PHPWS_Time::convertServerTime(mktime());
    }

    function getUserTZ()
    {
        $user_tz = PHPWS_Cookie::read('user_tz');

        if (empty($user_tz)) {
            return SERVER_TIME_ZONE;
        } else {
            return $user_tz;
        }
    }

    function getServerTime($utc_time)
    {
        return PHPWS_Time::convertUTCTime($utc_time, SERVER_TIME_ZONE);
    }

    function getUserTime($utc_time)
    {
        $user_tz = PHPWS_Time::getUserTZ();
        return PHPWS_Time::convertUTCTime($utc_time, $user_tz);
    }

    function convertUTCTime($time, $tz)
    {
        return $time + ($tz * 3600);
    }

    function convertServerTime($time)
    {
        return PHPWS_Time::convertUTCTime($time, -SERVER_TIME_ZONE);
    }

    function convertUserTime($time)
    {
        return PHPWS_Time::convertUTCTime($time, -PHPWS_Time::getUserTZ());
    }

}

?>