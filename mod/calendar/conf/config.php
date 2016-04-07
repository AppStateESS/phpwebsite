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
define('CALENDAR_TIME_FORMAT', '%I:%M %p');


// View title format
define('CALENDAR_DAY_HEADER', '%A %B %e, %Y');
define('CALENDAR_WEEK_HEADER', '%B %e');

// Determines whether to put the day before or after the month
define('CALENDAR_MONTH_FIRST', true);

// if true, adds a DTEND time for all day events
define('CALENDAR_HCAL_ALLDAY_END', false);


// %I %p = 01 PM - You can also use %H for 24 hour format
// %h    = 13
define('CALENDAR_TIME_FORM_FORMAT', '%I %p');
define('CALENDAR_TIME_MINUTE_INC', 15);

define('CALENDAR_TIME_LIST_FORMAT', '%I:%M %p');

// Controls the dimensions of the event editor popup
define('CALENDAR_EVENT_WIDTH', 700);
define('CALENDAR_EVENT_HEIGHT', 800);


// Controls the dimensions of the event editor popup
define('CALENDAR_SUGGEST_WIDTH', 600);
define('CALENDAR_SUGGEST_HEIGHT', 550);


// Controls the dimensions of the repeat editor popup
define('CALENDAR_REPEAT_WIDTH', 650);
define('CALENDAR_REPEAT_HEIGHT', 350);

// Repeats can get out of control. When this number is reached
// the calendar module will not allow anymore repeat writes
// to the database.
define('CALENDAR_MAXIMUM_REPEATS', 50);

// Total amount of allowed calendar suggestions
define('CALENDAR_TOTAL_SUGGESTIONS', 5);

// If true, events with single day start and end times will
// show the month, day, or year
define('CALENDAR_SAME_DAY_MDY', true);

/**
 * Date format for upcoming events window
 */
define('CALENDAR_UPCOMING_FORMAT', '%A, %e %b');
