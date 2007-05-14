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

    case version_compare($currentVersion, '2.1.0', '<'):
        $files = array('templates/module_list.tpl', 'img/boost.png');
        PHPWS_Boost::updateFiles($files, 'boost');
        $content[] = '<pre>2.1.0 Changes
-------------
+ RFE 1680787 - Boost module list shows full package title
+ Added code to create missing directories in updateFiles
+ Bug #1695102 - Added permission check before each module install or
  update.
+ Increase edit popup window height slightly
+ Added currentDone function.
+ Added German files
+ Changed over to new language format
+ Removed referenced constructor
+ Changed control panel icon
+ Changed backLink call to definite url to prevent loops.
+ Added ability to uninstall a module despite dependency settings.
</pre>';

    case version_compare($currentVersion, '2.1.1', '<'):
        $files = array('templates/module_list.tpl');
        PHPWS_Boost::updateFiles($files, 'boost');
        $content[] = '<pre>2.1.1 Changes
+ Boost now warns admin if Core update is needed.
</pre>';
    }

    return TRUE;
}

?>