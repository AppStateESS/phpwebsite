<?php

class PHPWS_Time {

    function getUTCTime()
    {
        return mktime() - (SERVER_TIME_ZONE * 3600);
    }

    function convertUTCTime($utc_time, $tz)
    {
        return $utc_time + ($tz * 3600);
    }

    function getServerTime($utc_time)
    {
        return PHPWS_Time::convertUTCTime($utc_time, SERVER_TIME_ZONE);
    }

    function getUserTime($utc_time)
    {
        $user_tz = PHPWS_Cookie::read('user_tz');

        if ( empty($user_tz) ) {
            return PHPWS_Time::getServerTime($utc_time);
        } else {
            return PHPWS_Time::convertUTCTime($utc_time, $user_tz);
        }
    }
}

?>