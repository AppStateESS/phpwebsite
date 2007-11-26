<?php
/**
 * Generic class extended by those offering or needing rides
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

define('RB_RIDER', 0);
define('RB_DRIVER', 1);

define('RB_MALE', 0);
define('RB_FEMALE', 1);

define('RB_NONSMOKER', 0);
define('RB_SMOKER', 1);

// works for all above
define('RB_EITHER', 2);

class RB_Ride {
    var $id            = 0;
    var $title         = null;
    var $ride_type     = RB_RIDER;
    var $user_id       = 0;
    var $s_location    = 0;
    var $d_location    = 0;
    var $depart_time   = 0;
    var $smoking       = RB_NONSMOKER;
    var $comments      = null;
    var $detour        = 0;
    var $gender_pref   = RB_EITHER;
    var $marked        = 0;

    function RB_Ride($id=0)
    {
        if (!$id) {
            $this->s_location = PHPWS_Settings::get('rideboard', 'default_slocation');
            return;
        }

        $this->id = (int)$id;
        $db = new PHPWS_DB('rb_ride');
        if (PHPWS_Error::logIfError($db->loadObject($this))) {
            $this->id = 0;
        }
    }
    
    function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }

    function setComments($comments)
    {
        $this->comments = trim(strip_tags($comments));
    }

    function save()
    {
        $db = new PHPWS_DB('rb_ride');
        if (!$this->user_id) {
            $this->user_id = Current_User::getId();
        }

        return $db->saveObject($this);
    }
}

?>