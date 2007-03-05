<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_update(&$content, $currentVersion)
{

    switch ($currentVersion) {
    case version_compare($currentVersion, '0.2.5', '<'):
        $content[] = 'This package does not update versions under 0.2.5';
        return false;

    case version_compare($currentVersion, '0.2.6', '<'):
        $content[] = '<pre>
0.2.6 changes
-------------
+ Moved search save to Volume class
+ Searches now reset search key words to prevent lost searches.
</pre>';

    case version_compare($currentVersion, '0.5.0', '<'):
        $db = new PHPWS_DB('webpage_page');
        $result = $db->addTableColumn('image_id', 'int NOT NULL default 0');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Failed adding image_id column to webpage_page table.';
            return false;
        }

        if (!PHPWS_DB::isTable('webpage_featured')) {
            $db2 = new PHPWS_DB('webpage_featured');
            $db2->addValue('id', 'int NOT NULL default 0');
            $db2->addValue('vol_order', 'int NOT NULL default 0');
            $result = $db2->createTable();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'Unable to create webpage_featured table.';
                return false;
            }
        }
        $content[] = '<pre>';
        $files = array('templates/featured.tpl',
                       'templates/page/basic.tpl',
                       'templates/page/prev_next.tpl',
                       'templates/page/short_links.tpl',
                       'templates/page/verbose_links.tpl',
                       'templates/header.tpl',
                       'templates/forms/edit_page.tpl',
                       'templates/forms/edit.tpl',
                       'conf/config.php',
                       'conf/error.php');

        if (PHPWS_Boost::updateFiles($files, 'webpage')) {
            $content[] = '+ The following files were updated successfully:';
        } else {
            $content[] = '+ The following files were NOT updated successfully:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
0.5.0 changes
-------------
+ Added simple image page inclusion.
+ Added "Featured" option. Lets you promote specific web pages summaries.
+ Added ability to move pages inside volumes.
+ Added ability to restore old headers and pages.
+ Fixed delete call from list link.
+ Moved error defines into their own file.
+ Added some missing error reporting.
+ Added simple image page addition.
+ Replaced a panic exit with an logged error and send to the
  errorPage.
+ Added page summary information to page templates.
+ Added a page select with onchange function. Put it into the basic
  page layout.
+ Moved a function out of Page getTpl for clarity.
+ Added ability to move pages.
+ Indexed page and volume table.
+ Added Feature permission.
+ Fixed some bugs with admin options.
+ Fixed submitting an empty command on list tab.
+ Removed table format from edit forms.
+ Added translate functions.
</pre>';

    case version_compare($currentVersion, '0.5.1', '<'):
        $db = new PHPWS_DB('webpage_volume');
        $db->addWhere('create_user_id', 0);
        $db->addValue('create_user_id', Current_User::getId());
        $result = $db->update();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'An error occurred while trying to update your webpage_volume table.';
            return false;
        }

        $content[] = '<pre>0.5.1 Changes
-------------';
        if (PHPWS_Boost::updateFiles(array('img/webpage.png'))) {
            $content[] = '+ Updated Web Page control panel icon.';
        } else {
            $content[] = '+ Unable to updated Web Page control panel icon.';
        }
$content[] = '
+ Updated created_user_id for converted pages.
+ Requesting a restricted page forwards user to the login screen.
</pre>';
    }

    return TRUE;
}


?>