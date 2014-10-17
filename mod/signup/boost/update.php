<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function signup_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>';

            $files = array('templates/slot_setup.tpl');
            signupUpdateFiles($files, $content);

            $content[] = '1.0.1 changes
----------------
+ Added ability to reset slot order should it come unraveled.
+ Fixed reroute link that was hard coded to go to sheet id 1.</pre>';

        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>1.0.2 changes
----------------
+ Changed email to send individually.
+ Fixed: "All slots full" message was not displaying.</pre>';

        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '<pre>';
            PHPWS_Boost::registerMyModule('signup', 'users', $content);
            $db = new PHPWS_DB('signup_sheet');
            if (PHPWS_Error::logIfError($db->addTableColumn('contact_email', 'varchar(255) default NULL'))) {
                $content[] = '--- Failed creating new column on signup_sheet.</pre>';
                return false;
            } else {
                $content[] = '--- contact_email column created successfully on signup_sheet table.';
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('multiple', 'smallint NOT NULL default 0'))) {
                $content[] = '--- Failed creating new column on signup_sheet.</pre>';
                return false;
            } else {
                $content[] = '--- "multiple" column created successfully on signup_sheet table.';
            }

            $files = array('templates/peep_pop.tpl', 'templates/slot_setup.tpl', 'templates/edit_sheet.tpl',
                'templates/peeps.tpl', 'templates/slot_setup.tpl', 'img/edit.png', 'img/delete.png');
            signupUpdateFiles($files, $content);


            $content[] = '1.1.0 changes
----------------
+ Added way to update slots
+ Added user permissions to signup
+ Removed some unneeded, commented code
+ Added alphabetic reordering
+ Fixed ordering up and downs.
+ Changed slot form to selection method. Previously showed all slots
  causing long page loads on big signups.
+ Added contact email address for sheets.
+ Added url forwarding support.
+ Sheets can be set to allow multiple signups.
</pre>';

        case version_compare($currentVersion, '1.1.1', '<'):
            $content[] = '<pre>';
            signupUpdateFiles(array('templates/sheet_list.tpl', 'templates/slot_setup.tpl'), $content);
            $content[] = '1.1.1 changes
-------------------
+ Restricted users cannot create signup sheets.
+ Added search textfield to slot screen.
+ Added missing navigation links to sheet listing.</pre>';

        case version_compare($currentVersion, '1.1.2', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('signup_sheet');
            if (PHPWS_Error::logIfError($db->addTableColumn('contact_email', 'varchar(255) default NULL'))) {
                $content[] = '--- Failed creating new column on signup_sheet.</pre>';
                return false;
            } else {
                $content[] = '--- contact_email column created successfully on signup_sheet table.';
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('multiple', 'smallint NOT NULL default 0'))) {
                $content[] = '--- Failed creating new column on signup_sheet.</pre>';
                return false;
            } else {
                $content[] = '--- "multiple" column created successfully on signup_sheet table.';
            }


            $content[] = '1.1.2 changes
-------------------
+ Install sql was missing new columns in signup_sheet table.
+ Removed the phone number parsing. Got in the way of extensions and
  the like.
</pre>';

        case version_compare($currentVersion, '1.2.0', '<'):
            $content[] = '<pre>1.2.0 changes
----------------
+ Fixed: signup errors reseting slot pick
+ Removed redundant error message
+ previous register fix would not list empty slots.
+ Removed string length on phone number
+ Only pulling registered users for slots open.
+ PHP 5 formatted.
</pre>';

        case version_compare($currentVersion, '1.2.1', '<'):
            $content[] = '<pre>1.2.1 changes
----------------
+ Removed reference symbols
+ Added dngettext for "openings(s)" translation
+ Rewrote getAllSlots. The slots filled number wasn\'t joining
  properly.
+ Fixed sheet view link.</pre>';

        case version_compare($currentVersion, '1.2.2', '<'):
            $content[] = '<pre>1.2.2 changes
----------------
+ Fixed url sent to key.</pre>';

        case version_compare($currentVersion, '1.3.0', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('signup_peeps');
            $db->addTableColumn('extra1', 'varchar(255) null');
            $db->addTableColumn('extra2', 'varchar(255) null');
            $db->addTableColumn('extra3', 'varchar(255) null');
            $db->query('update signup_peeps set extra1 = organization');
            $db = new PHPWS_DB('signup_sheet');
            $db->addTableColumn('extra1', 'varchar(255) null');
            $db->addTableColumn('extra2', 'varchar(255) null');
            $db->addTableColumn('extra3', 'varchar(255) null');
            $db->addValue('extra1', 'Organization');
            $db->update();

            $files = array('templates/applicants.tpl', 'templates/edit_peep.tpl',
                'templates/peeps.tpl', 'templates/signup_form.tpl');
            signupUpdateFiles($files, $content);
            $content[] = '1.3.0 changes
--------------
+ Added extra 1 thru 3 to sheet and peeps for extra questions.
</pre>';

        case version_compare($currentVersion, '1.3.1', '<'):
            $content[] = '<pre>1.3.1 changes
-------------------
+ Fixed incorrect counting of people in slots.</pre>';

        case version_compare($currentVersion, '1.3.2', '<'):
            $content[] = '<pre>1.3.2 changes
-------------------
+ PHP 5 strict fixes.
+ Icon class added</pre>';

        case version_compare($currentVersion, '1.3.3', '<'):
            $content[] = '<pre>1.3.3 changes
-------------------
+ New additions by Chris Coley
 - Added move to bottom and top for slot ordering.
 - Phone number error checked.
 - Refined slot search, only slots with searched member.
 - Start and end times order options on sheet listing.
 - Added clearer instructions to sheet setup.
 - Emails sent according to last search.
 - UI additions to ease administration.
</pre>';
        case version_compare($currentVersion, '1.3.4', '<'):
            $content[] = '<pre>1.3.4 changes
-----------------
+ Fixed bad form on moving slot member</pre>';
        case version_compare($currentVersion, '1.3.5', '<'):
            $db = \Database::newDB();
            $sp = $db->addTable('signup_peeps');
            if (!$sp->columnExists('extra1')) {
                $dt1 = \Database\Datatype::factory($sp, 'extra1', 'varchar');
                $dt1->add();
                $dt2 = \Database\Datatype::factory($sp, 'extra2', 'varchar');
                $dt2->add();
                $dt3 = \Database\Datatype::factory($sp, 'extra3', 'varchar');
                $dt3->add();
            }
            $content[] = '<pre>1.3.5 changes
----------------
+ Installation did not include the extra columns.
</pre>';

        case version_compare($currentVersion, '1.3.6', '<'):
            $db = \Database::newDB();
            $dbtype = $db->getDatabaseType();
            if ($dbtype == 'mysql') {
                $db->query('ALTER TABLE `signup_peeps` MODIFY extra1 VARCHAR(255) DEFAULT NULL');
                $db->query('ALTER TABLE `signup_peeps` MODIFY extra2 VARCHAR(255) DEFAULT NULL');
                $db->query('ALTER TABLE `signup_peeps` MODIFY extra3 VARCHAR(255) DEFAULT NULL');
            } else {
                $db->query('ALTER TABLE signup_peeps ALTER COLUMN extra1 DROP NOT NULL');
                $db->query('ALTER TABLE signup_peeps ALTER COLUMN extra2 DROP NOT NULL');
                $db->query('ALTER TABLE signup_peeps ALTER COLUMN extra3 DROP NOT NULL');
            }
            $content[] = '<pre>1.3.6 changes
----------------
+ Extra peer columns need to be null
</pre>';
    }
    return true;
}

function signupUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'signup')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>