<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function checkin_update(&$content, $current_version) {
    switch (1) {
        case version_compare($current_version, '1.0.1', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('checkin_staff');

            if (PHPWS_Error::logIfError($db->addTableColumn('view_order', 'smallint not null default 0'))) {
                $content[] = 'Unable to create checkin_staff.view_order column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_staff.view_order column.';
            }

            $db->addColumn('id');
            $staff_list = $db->select('col');

            if (!empty($staff_list)) {
                $count = 1;
                foreach ($staff_list as $staff_id) {
                    $db->reset();
                    $db->addWhere('id', $staff_id);
                    $db->addValue('view_order', $count);
                    PHPWS_Error::logIfError($db->update());
                    $count++;
                }
            }

            checkinUpdateFiles(array('templates/visitors.tpl',
                                 'templates/waiting.tpl',
                                 'templates/queue.tpl', 'templates/settings.tpl'), $content);

            $content[] = '1.0.1 changes
------------------
+ Fixed bug with pulling current staff member
+ Added refresh link to waiting and assignment page
+ Fixed report
</pre>';

        case version_compare($current_version, '1.0.2', '<'):
            $content[] = '<pre>';
            checkinUpdateFiles(array('templates/visitors.tpl',
                                 'templates/waiting.tpl',
                                 'templates/style.css'), $content);
            $content[] = '1.0.2 changes
--------------------
+ Fixed translation typo.
+ Added "Send back" condition</pre>';

        case version_compare($current_version, '1.0.3', '<'):
            $content[] = '<pre>';
            checkinUpdateFiles(array('templates/report.tpl'), $content);
            $content[] = '1.0.3 changes
--------------------
+ Removed error message from report if no reasons created
+ Added the time of arrival to the report
+ Changed report date entry interface
+ Upper cased names.
</pre>';

        case version_compare($current_version, '1.0.4', '<'):
            $content[] = '<pre>1.0.4 changes
---------------------
+ Fixed waiting time setting</pre>';

        case version_compare($current_version, '1.1.0', '<'):
            $content[] = '<pre>1.1.0 changes
---------------------
+ Added code to prevent refreshed duplicates
+ Fixed possible error in admin view
+ Added monthly and student reports
+ Added report for number of times a visitor has visited within 30 days.
+ PHP 5 Strict changes</pre>';

        case version_compare($current_version, '1.1.1', '<'):
            $content[] = '<pre>1.1.1 changes
---------------------
+ Reports limited to admins</pre>';

        case version_compare($current_version, '1.2', '<'):
            $db = new PHPWS_DB('checkin_staff');
            $db->addTableColumn('active', 'smallint not null default 1');
            $content[] = '<pre>1.2 changes
--------------
+ Fixed blue button on admin menu
+ Staff can now be deactivated so they appear on reports but do not receive visitors</pre>';

        case version_compare($current_version, '1.3', '<'):
            $db = new PHPWS_DB('checkin_visitor');
            $db->addTableColumn('email', 'varchar(255) NULL');
            $content[] = '<pre>1.3 changes
---------------
+ Option to collect visitor email addresses.</pre>';
        case version_compare($current_version, '1.4.0', '<'):
            $content[] = '<pre>1.4.0 changes
---------------
+ May now report by visitor name.</pre>';
        case version_compare($current_version, '1.5.0', '<'):
            $content[] = '<pre>';
            
            // Make changes to checkin_visitor table
            $db = new PHPWS_DB('checkin_visitor');
            if (PHPWS_Error::logIfError($db->addTableColumn('gender', 'varchar(20) default NULL'))) {
                $content[] = 'Unable to create checkin_visitor.gender column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_visitor.gender column.';
            }
            if (PHPWS_Error::logIfError($db->addTableColumn('birthdate', 'varchar(20) default NULL'))) {
                $content[] = 'Unable to create checkin_visitor.birthdate column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_visitor.birthdate column.';
            }

            // Make changes to checkin_staff table
            $db = new PHPWS_DB('checkin_staff');
            if (PHPWS_Error::logIfError($db->addTableColumn('birthdate_filter_end', 'varchar(20) default NULL', 'f_regexp'))) {
                $content[] = 'Unable to create checkin_staff.birthdate_filter_end column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_staff.birthdate_filter_end column.';
            }
            if (PHPWS_Error::logIfError($db->addTableColumn('birthdate_filter_start', 'varchar(20) default NULL', 'f_regexp'))) {
                $content[] = 'Unable to create checkin_staff.birthdate_filter_start column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_staff.birthdate_filter_start column.';
            }
            if (PHPWS_Error::logIfError($db->addTableColumn('gender_filter', 'varchar(20) default NULL', 'f_regexp'))) {
                $content[] = 'Unable to create checkin_staff.gender_filter column.</pre>';
                return false;
            } else {
                $content[] = 'Created checkin_staff.gender_filter column.';
            }
            if (PHPWS_Error::logIfError($db->query('ALTER TABLE checkin_staff CHANGE filter lname_filter varchar(255) default NULL'))) {
                $content[] = 'Unable to rename checkin_staff.filter column.</pre>';
                return false;
            } else {
                $content[] = 'Renamed checkin_staff.filter to checkin_staff.lname_filter.';
            }
            if (PHPWS_Error::logIfError($db->query('ALTER TABLE checkin_staff CHANGE f_regexp lname_regexp varchar(255) default NULL'))) {
                $content[] = 'Unable to rename checkin_staff.f_regexp column.</pre>';
                return false;
            } else {
                $content[] = 'Renamed checkin_staff.f_regexp to checkin_staff.lname_regexp.';
            }

            $content[] = '1.5.0 changes
---------------
+ Fixed the "print view" for daily reports.
+ Option to collect visitor gender.
+ Option to collect visitor birthdate.
+ Added staff filters for gender and birthdate.
+ Staff can now have more than one filter.</pre>';
    }
    return true;
}

function checkinUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'checkin')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>
