<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function calendar_update(&$content, $version)
{
    switch ($version) {
        case version_compare($version, '1.2.2', '<'):
            $content[] = 'This package will not update any versions prior to 1.2.2.';
            return false;

        case version_compare($version, '1.3.0', '<'):
            $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/calendar/boost/sql_update_130.sql');
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = '+ Unable to import new suggestion table.';
                return false;
            } else {
                $files = array('templates/admin/forms/edit_event.tpl',
                           'templates/view/day.tpl',
                           'templates/view/week.tpl',
                           'templates/view/month/grid.tpl',
                           'templates/view/month/list.tpl',
                           'templates/style.css',
                           'templates/user_main.tpl',
                           'templates/admin/approval.tpl',
                           'conf/config.php');
                if (PHPWS_Boost::updateFiles($files, 'calendar')) {
                    $content[] = '+ Template files updated successfully.';
                } else {
                    $content[] = '+ Unable to copy template files locally.';
                    $content[] = '<pre>' . implode("\n", $files) . '</pre>';
                }

                $content[] = '+ Suggestion table import successful';
                $content[] = '<pre>
1.3.0 Changes
-------------
+ Added ability to for anonymous users to make event suggestions.
+ Fixed some issues with javascript disabled users unable to make events.
+ First public schedule is made the default on creation.
</pre>';
            }

        case version_compare($version, '1.4.0', '<'):
            $content[] = "<pre>1.4.0 Changes\n-------------";
            $files = array('templates/admin/settings.tpl', 'conf/config.php');
            if (PHPWS_Boost::updateFiles($files, 'calendar')) {
                $content[] = '+ Successfully copied these files locally:';
            } else {
                $content[] = '+ Could not copy these files locally:';
            }

            $content[] = '    ' . implode("\n    ", $files);
            $content[] = '+ Bug #1648965. Clicking Day or Week from grid view pulls correct day
+ Changed "Today" link to view appropiate description to prevent
  confusion
+ Changed time defaults to %p to work with more installs
+ If start date is greater than end date, the event will be saved to
  the save day.
+ Bug #1648963. Fixed mini month not showing days with events.
+ Made event count linkable in grid view
+ Labeled some permissions "unrestricted only"
+ Added permissions checks for editing and deleting
+ Created checkPermissions function in schedule class to simplify code
+ Removed commented legacy code from Schedule.php
+ Added missing suggestion tables to install and uninstall
+ Fixed caching logic
+ Fixed default schedule loading in Calendar class
+ Fixed inability to create more than one schedule
+ Added option to disable month view caching
+ Caching now works on public calendars only.
+ Added translate functions.
+ Updated message translation files.</pre>';

        case version_compare($version, '1.4.1', '<'):
            $content[] = "<pre>1.4.1 Changes\n-------------";
            $files = array('templates/admin/forms/edit_event.tpl', 'javascript/check_date/head.js');
            if (PHPWS_Boost::updateFiles($files, 'calendar')) {
                $content[] = '+ Successfully updated the following files:';
            } else {
                $content[] = '+ Unable to update the following files:';
            }
            $content[] = '    ' . implode("\n    ", $files);
            $content[] = '+ Made noon the default time for new events.
+ Fixed Daylight Saving Time breaking repeats.
+ Delete event links made secure.
+ Days made linkable in month list view.
+ Added a sync dates button on the edit event form.';

        case version_compare($version, '1.5.0', '<'):
            $db = new PHPWS_DB('calendar_schedule');
            $result = $db->addTableColumn('show_upcoming', 'SMALLINT NOT NULL DEFAULT 0');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Could not create show_upcoming column in calendar_schedule table.';
                return false;
            }
            $content[] = '<pre>';
            $files = array('img/calendar.png', 'conf/config.php',
                       'templates/admin/forms/edit_schedule.tpl',
                       'templates/style.css', 'templates/view/upcoming.tpl');

            if (PHPWS_Boost::updateFiles($files, 'calendar')) {
                $content[] = '-- Successfully updated the following files:';
            } else {
                $content[] = '-- Unable to update the following files:';
            }
            $content[] = '    ' . implode("\n    ", $files);

            $content[] = '
1.5.0 Changes
---------------------
+ Can now choose to show upcoming events.
+ Removed schedule created message when using javascript popup
+ Moved getEvents function in the Schedule class
+ Increase calendar event form\'s popup height
+ Reformated schedule form
+ Bug # 1699659 - Calendar will not show "Add Event" link if a
  schedule has not been created. Instead, the "Create schedule" link
  will appear.
+ Updated language format
</pre>';

        case version_compare($version, '1.5.1', '<'):
            $db = new PHPWS_DB('calendar_schedule');
            if (!$db->isTableColumn('show_upcoming')) {
                $result = $db->addTableColumn('show_upcoming', 'SMALLINT NOT NULL DEFAULT 0');
                if (PHPWS_Error::logIfError($result)) {
                    $content[] = '--- Could not create show_upcoming column in calendar_schedule table.</pre>';
                    return false;
                }
            }
            $content[] = '<pre>';
            $files = array('conf/config.php', 'templates/view/month/list.tpl',
                       'templates/view/day.tpl', 'templates/view/week.tpl');

            if (PHPWS_Boost::updateFiles($files, 'calendar')) {
                $content[] = '-- Successfully updated the following files:';
            } else {
                $content[] = '-- Unable to update the following files:';
            }
            $content[] = '    ' . implode("\n    ", $files);

            $content[] = '
1.5.1 Changes
---------------------
+ 1.5.0 installations sql import was missing the show_upcoming column.
+ Added define to prevent day month year printing on same day events.
+ "Add event" links added to some views.
+ Changed the default hour format to "I" (eye) from l (ell) in
  config.php. Some php configurations do not recognize it.
</pre>';

        case version_compare($version, '1.5.2', '<'):
            $content[] = '<pre>';
            calendarUpdateFiles(array('templates/style.css'), $content);
            $content[] = '1.5.2 changes
-----------
+ Removed calendar colors that matched default theme.
+ Added Spanish translation.</pre>';

        case version_compare($version, '1.5.3', '<'):
            $content[] = '<pre>';
            calendarUpdateFiles(array('templates/style.css'), $content);
            $content[] = '1.5.3 change
-----------
+ Fixed issues with js_calendar.</pre>';

        case version_compare($version, '1.6.0', '<'):
            $content[] = '<pre>';
            $files = array('templates/style.css', 'templates/admin/forms/blog.tpl',
                       'templates/admin/settings.tpl', 'templates/view/day.tpl',
                       'templates/view/event.tpl', 'templates/view/month/grid.tpl',
                       'templates/view/month/list.tpl');
            calendarUpdateFiles($files, $content);
            $content[] = '1.6.0 changes
-------------------
+ Month grid view can be set to show events
+ Calendar events can be posted in the blog
+ Paragraph tag around day message
+ Fixed comment in event tpl preventing bar from disappearing in user
  mode
+ Removed extra message tag from month list view.
+ Applied patch #1813081 from aDarkling</pre>';

        case version_compare($version, '1.6.1', '<'):
            $content[] = '<pre>
1.6.1 changes
-------------
+ Fixed call to absent function in Event.php</pre>';

        case version_compare($version, '1.6.2', '<'):
            $content[] = '<pre>';
            calendarUpdateFiles(array('conf/config.php', 'javascript/check_date/head.js'), $content);
            $content[] = '1.6.2 changes
-------------
+ Added a upcoming date format
+ Fixed: Day view doesn\'t allow you to add an event until you create a schedule.
+ Deleting the default public calendar resets the selection.
+ Fixed: Bug #1879356 - Events spanning multiple days do not autocorrect time.
</pre>';

        case version_compare($version, '1.6.3', '<'):
            $content[] = '<pre>1.6.3 changes
-------------
+ Added missing repeatYearly function.</pre>';

        case version_compare($version, '1.6.4', '<'):
            $content[] = '<pre>';
            calendarUpdateFiles(array('templates/admin/schedules.tpl'), $content);
            $content[] = '1.6.4 changes
-------------
+ Added missing pager navigation tags to schedule listing.</pre>';

        case version_compare($version, '1.7.0', '<'):
            $db = new PHPWS_DB('calendar_schedule');
            $db->addColumn('id');
            $schedules = $db->select('col');
            if (!empty($schedules)) {
                foreach ($schedules as $id) {
                    $event_db = new PHPWS_DB('calendar_event_' . $id);
                    $event_db->addColumn('key_id');
                    $keys = $event_db->select('col');
                    if (!empty($keys)) {
                        $key_db = new PHPWS_DB('phpws_key');
                        $key_db->addWhere('id', $keys);
                        $key_db->addValue('item_name', 'event' . $id);
                        PHPWS_Error::logIfError($key_db->update());
                    }
                }
            }

            $content[] = '<pre>';
            $files = array('img/', 'templates/admin/schedules.tpl', 'templates/view/month/grid.tpl',
                       'templates/view/month/list.tpl', 'templates/view/day.tpl', 'templates/view/event.tpl',
                       'templates/view/week.tpl', 'templates/ical.tpl', 'templates/style.css',
                       'templates/upload.tpl', 'templates/admin/settings.tpl');
            calendarUpdateFiles($files, $content);
            $content[] = '1.7.0 changes
-------------
+ Added ability to hide the mini grid calendar.
+ Increased edit event popup default size.
+ Moved some frequent functions closer to top of switch
+ Added ability to upload and download events using iCal format
+ Fixed: Calendar was saving all events from all schedules using the same item name.
+ Deleting a schedule will remove event keys as well.
+ Deleting an event clears the cache to prevent ghosts in grid view.
+ If a non existing event is accessed, calendar forwards to day view instead of 404.
+ Added check that removes the default public calendar if it is
  changed to private.
+ Fixed events showing on multiple schedules.
+ php 5 formatted.
</pre>';

        case version_compare($version, '1.7.1', '<'):
            $content[] = '<pre>';
            $files = array('templates/admin/settings.tpl');
            calendarUpdateFiles($files, $content);
            $content[] = '1.7.1 changes
------------------
+ Added option to enable rel="nofollow" on date links</pre>';

        case version_compare($version, '1.7.2', '<'):
            $content[] = '<pre>1.7.2 changes
------------------
+ Fixed event ical download</pre>';

        case version_compare($version, '1.7.3', '<'):
            $content[] = '<pre>1.7.3 changes
------------------
+ Fixed empty events check in Upcoming Events
+ PHP 5 strict fixes.</pre>';
        case version_compare($version, '1.7.4', '<'):
            $content[] = '<pre>1.7.4 changes
------------------
+ RSS added.</pre>';

        case version_compare($version, '1.7.5', '<'):
            $content[] = '<pre>1.7.5 changes
------------------
+ Added an explicit require for Time.php in Calendar, in an attempt to move off of the older autoload</pre>';

        case version_compare($version, '1.7.6', '<'):
            $content[] = '<pre>1.7.6 changes
------------------
+ Bootstrap styling added
+ Updated Font Awesome icons
+ Fixed schedule select with last Form updates
+ Fixed users not seeing public calendars
</pre>';
        case version_compare($version, '1.8.0', '<'):
            $content[] = <<<EOF
<pre>1.8.0
-------------
+ Added END_TIME_HOUR, END_TIME_MONTH, and END_TIME_YEAR
  template tags to the event view.
+ Added Bootstrap icons and navigation buttons.
+ Administrative forms switched over to modal windows.
+ Using new datetimepicker javascript.</pre>
EOF;
        case version_compare($version, '1.8.1', '<'):
            $content[] = <<<EOF
<pre>1.8.1
-------------
+ Rolled back array call specific to PHP 5.4.
+ Increased specificity of .date-label CSS class.
</pre>
EOF;
    } // end of switch

    return true;
}

function calendarUpdateFiles($files, &$content) {
    if (PHPWS_Boost::updateFiles($files, 'calendar')) {
        $content[] = '-- Successfully updated the following files:';
    } else {
        $content[] = '-- Unable to update the following files:';
    }
    $content[] = '    ' . implode("\n    ", $files);
    $content[] = '';
}
