<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '1.9.7', '<'):
        $content[] = 'Updates for versions under 1.9.7 may not be updated with this package.';
        return false;

    case version_compare($currentVersion, '1.9.8', '<'):
        $content[] = '<pre>
1.9.8 Changes
-------------
+ Removed the old loadAsMod functions for core.
+ Core updates config, image, template, and javascript files.
+ Added dependency ability for core.
</pre>';

    case version_compare($currentVersion, '1.9.9', '<'):
        if (PHPWS_Boost::updateFiles(array('templates/module_list.tpl', 'boost'), 'boost')) {
            $content[] = 'module_list.tpl file copied successfully.';
        } else {
            $content[] = 'Failed to copy module_list.tpl file successfully.';
        }
        $content[] = '<pre>
1.9.9 Changes
-------------
+ Changed updateFiles to return false even with an error. Now logs
  the error instead of returning it.
+ Added link to check all modules for updates at once.
</pre>';

    case version_compare($currentVersion, '2.0.0', '<'):
        $content[] = '<pre>2.0.0 Changes
-------------
+ Added error check for version checking Bug #1606366
+ Boost now reports on the directories it deletes.
+ Boost now only deletes image and file directories if defined on install.
+ Added translate functions.</pre>';
    }

    return TRUE;
}

?>