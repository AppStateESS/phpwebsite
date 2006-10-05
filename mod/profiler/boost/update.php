<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function profiler_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.1.0', '<'):
        PHPWS_DB::dropTable('profiler_item_permissions');


    case version_compare($currentVersion, '0.1.2', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/forms/division_list.tpl'), 'profiler')) {
            $content[] = 'Copied new division list template locally.';
        } else {
            $content[] = 'Unable to copy template locally.';
            return false;
        }

    case version_compare($currentVersion, '0.1.4', '<'):
        $db = & new PHPWS_DB('profiler_division');
        $result = $db->renameTableColumn('show_sidebar', 'show_homepage');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to update profiler.';
            return false;
        } else {
            $content[] = 'Moved profile display to home page.';
        }
        PHPWS_Boost::updateFiles(array('conf/config.php'));

    case version_compare($currentVersion, '0.2.0', '<'):
        $files = array();
        $files[] = 'conf/config.php';
        $files[] = 'templates/homepage.tpl';
        $files[] = 'templates/style.css';
        $files[] = 'templates/views/large.tpl';
        $files[] = 'templates/views/small.tpl';
        $files[] = 'templates/forms/settings.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'profiler');
        if (PEAR::isError($result)) {
            $content[] = 'Unable to copy template files locally. Please update manually.';
        } else {
            $content[] = 'New template files copied successfully.';
        }
        $content[] = '- Added parseTag call to getFullStory function.';
        $content[] = '- Changed side bar view to homepage view';
        $content[] = '- Added division view';

    case version_compare($currentVersion, '0.3.0', '<'):
        $db = & new PHPWS_DB('profiles');
        $result = $db->addTableColumn('email', 'varchar(60) NULL default \'\'');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to add new column to the profiles table.';
            return false;
        }
        $result = $db->addTableColumn('website', 'varchar(255) NULL default \'\'');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to add new column to the profiles table.';
            return false;
        }

        $files = array();
        $files[] = 'templates/forms/edit.tpl';
        $files[] = 'templates/view/large.tpl';
        $files[] = 'templates/homepage.tpl';
        $files[] = 'img/email.png';
        $files[] = 'img/website.png';
        PHPWS_Boost::updateFiles($files, 'profiler');

        $content[] = 'New - email and web site address listings added to profiles.';
    }     
    
    return true;
}

?>