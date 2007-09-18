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
        if (PEAR::isError($result)) {
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
+ Fixed issue with js_calendar.</pre>';


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

?>