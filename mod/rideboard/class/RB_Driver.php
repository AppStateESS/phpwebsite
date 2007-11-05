<?php
/**
 * Class for those offering rides.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('rideboard', 'Ride.php');

class RB_Driver extends RB_Ride {
    var $departure_time  = 0;
    var $return_time     = 0;
    var $detour_distance = 0;
}

?>