<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function calendar_update(&$content, $version)
{

    switch ($version) {
    case version_compare($version, '1.1.0', '<'):
        $files[] = 'templates/style.css';
        $files[] = 'templates/admin/settings.tpl';
        $files[] = 'templates/admin/forms/edit_schedule.tpl';
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Failed to copy template files.';
        }
        $content[] = 'New - event displays as Busy to the public if set as such.';
        $content[] = 'New - Settings tab returns with a few basic settings.';

    case version_compare($version, '1.2.0', '<'):
        $files = array('templates/admin/forms/settings.tpl');
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Failed to copy template files.';
        }
        
        $content[] = '- Opened up private calendar key posting to allow permission settings.';
        $content[] = '- Added admin option to change the default calendar view.';
        $content[] = '- Month link on mini calendar now opens the default view.';
        $content[] = '- Public calendars that are restricted are now properly hidden.';

    case version_compare($version, '1.2.1', '<'):
        $files = array();
        $files[] = 'templates/admin/forms/setting.tpl';
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Failed to copy template files.';
        }

        $content[] = '<pre>
+ Updated file - templates/admin/forms/setting.tpl
+ Fixed bug #1589525 - Calendar days not linked to correct day view.
+ Fixed bug #1589528 - Added option to show mini calendar on all
  pages, front only, or none to settings tab.
+ Added language file.
+ Updated files templates/admin/forms/settings.tpl
+ Opened up private calendar key posting to allow permission settings.
+ Added admin option to change the default calendar view
+ Month link on mini calendar now opens the default view.
+ Public calendars that are restricted are now properly hidden.
</pre>';

    case version_compare($version, '1.2.2', '<'):
        $files = array();
        $files[] = 'templates/admin/forms/setting.tpl';
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Failed to copy template files.';
        }

        $content[] = '<pre>
1.2.2 Changes
-------------
+ Fixed mini calendar changing date when viewing other months
+ Creating a new event will now properly use the currently viewed date
+ Added reset cache link to miniadmin
+ Added setting to show day links on mini calendar only if there is an
  event on that day (Bug #1596779).
+ Added caching to mini calendar
+ Opened caching on grid regardless of log status
+ Events now appear on previous and next months in grid view
  (Bug #1596780) 
</pre>';

    case version_compare($version, '1.3.0', '<'):
        $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/calendar/boost/sql_update_130.sql');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = '+ Unable to import new suggestion table.';
            return false;
        } else {
            $content[] = '+ Suggestion table import successful';
            $content[] = '<pre>
1.3.0 Changes
-------------
+ Added ability to for anonymous users to make event suggestions.
</pre>';

        }
        break;

    }

    return true;
}

?>