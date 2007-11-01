<?php
/**
 * Generic class extended by those offering or needing rides
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class RB_Ride {
    var $id            = 0;
    var $user_id       = 0;
    var $s_location    = 0;
    var $d_location    = 0;
    var $allow_smoking = false;
    var $gender_pref   = 0;
    var $comments      = null;
    var $contact_email = null;
    var $views         = 0;
}

?>