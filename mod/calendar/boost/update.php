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
        
    }

    return true;
}

?>