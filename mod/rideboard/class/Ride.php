<?php
/**
 * Generic class extended by those offering or needing rides
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class RB_Ride {
    var $id            = 0;
    var $ride_type     = 0;
    var $user_id       = 0;
    var $s_location    = 0;
    var $d_location    = 0;
    var $depart_time   = 0;
    var $smoking       = false;
    var $comments      = null;
    var $detour        = 0;
}

?>