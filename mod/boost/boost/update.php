<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '1.8.5', '<'):
        $files = array();
        $files[] = 'templates/check_update.tpl';
        if (!PHPWS_Boost::updateFiles($files, 'boost')) {
            $content[] = 'Unable to update local template files.';
        }
        $content[] = '+ Added a "core" update option.';
        $content[] = '+ Fixed check not listing changes in updated modules.';
        $content[] = '+ Fixed file updates not copying if in subdirectory.';
        $content[] = '+ Updated modules now show current version and version to which they will be updated.';

    case version_compare($currentVersion, '1.8.6', '<'):
        $content[] = '+ Added core update functionality.';
        $content[] = '+ Authorization keys added to links for extra security.';
        
    case version_compare($currentVersion, '1.9.0', '<'):
        $content[] = 'New - Added call to clear the cache on a module\'s removal.';
        $content[] = 'New - Added call to clear a module\'s settings upon removal.';
        $content[] = 'New - Recoded call to unregister a module\'s keys on removal.';
        $content[] = 'New - Now reports uncopyable file in log.';

    case version_compare($currentVersion, '1.9.1', '<'):
        $content[] = 'Fix - updateFiles was only quitting if new files were identical.';

    case version_compare($currentVersion, '1.9.2', '<'):
        $content[] = 'Fix - Boost now tracks core dependencies properly.';

    case version_compare($currentVersion, '1.9.3', '<'):
        $content[] = 'Fix - Boost was pulling Core\'s version from the file not the db.';

    case version_compare($currentVersion, '1.9.4', '<'):
        $content[] = 'New - Boost will now check read dependencies included in the check.xml file.';
        $files = array();
        $files[] = 'templates/check_update.tpl';
        PHPWS_Boost::updateFiles($files, 'boost');

    case version_compare($currentVersion, '1.9.5', '<'):
        $content[] = 'Boost now updates installed branches.';

    case version_compare($currentVersion, '1.9.6', '<'):
        $content[] = 'Small changes to work with Branches better.';

    case version_compare($currentVersion, '1.9.7', '<'):
        $content[] = 'Fixed - Check link will appear regardless of directory permission.';

    case version_compare($currentVersion, '1.9.8', '<'):
        $content[] = '<pre>
1.9.8 Changes
-------------
+ Removed the old loadAsMod functions for core.
+ Core updates config, image, template, and javascript files.
+ Added dependency ability for core.
</pre>';
    }

    return TRUE;
}

?>