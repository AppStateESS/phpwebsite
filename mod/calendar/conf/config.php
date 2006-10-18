<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

// choices here are month_list, month_grid, day, and week
define('DEFAULT_CALENDAR_VIEW', 'day');

// Please read http://www.php.net/manual/en/function.strftime.php
// before changing these values
define('CALENDAR_DATE_FORMAT', '%B %e, %Y');
define('CALENDAR_TIME_FORMAT', '%l:%M %P');


// View title format
define('CALENDAR_DAY_HEADER', '%A %B %e, %Y');
define('CALENDAR_WEEK_HEADER', '%B %e');

// Determines whether to put the day before or after the month
define('CALENDAR_MONTH_FIRST', true);

// if true, adds a DTEND time for all day events
define('CALENDAR_HCAL_ALLDAY_END', false);


// %l %p = 1 PM
// %h    = 13
define('CALENDAR_TIME_FORM_FORMAT', '%l %p');
define('CALENDAR_TIME_MINUTE_INC', 15);

define('CALENDAR_TIME_LIST_FORMAT', '%l:%M %P');

// Controls the dimensions of the event editor popup
define('CALENDAR_EVENT_WIDTH', 700);
define('CALENDAR_EVENT_HEIGHT', 650);

// Controls the dimensions of the repeat editor popup
define('CALENDAR_REPEAT_WIDTH', 650);
define('CALENDAR_REPEAT_HEIGHT', 350);

// Repeats can get out of control. When this number is reached
// the calendar module will not allow anymore repeat writes
// to the database.
define('CALENDAR_MAXIMUM_REPEATS', 50);
?>