<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

  // Please read http://www.php.net/manual/en/function.strftime.php
  // before changing these values

define('CALENDAR_DAY_FORMAT', '%B %e, %Y');

define('CALENDAR_MONTH_LISTING', '%B');

// %l %p = 1 PM
// %h    = 13
define('CALENDAR_TIME_FORM_FORMAT', '%l %p');

define('CALENDAR_TIME_MINUTE_INC', 15);

// Controls the dimensions of the event editor popup
define('CALENDAR_EVENT_WIDTH', 700);
define('CALENDAR_EVENT_HEIGHT', 650);

?>