<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '2.1.0', '<'):
        $content[] = '<pre>Boost versions prior to 2.1.0 are not supported.
Please download update 2.1.1.</pre>';
        break;

    case version_compare($currentVersion, '2.1.1', '<'):
        $files = array('templates/module_list.tpl');
        PHPWS_Boost::updateFiles($files, 'boost');
        $content[] = '<pre>2.1.1 Changes
+ Boost now warns admin if Core update is needed.
</pre>';

    case version_compare($currentVersion, '2.1.2', '<'):
        $content[] = '<pre>';
        $files = array('templates/module_list.tpl');
        if (PHPWS_Boost::updateFiles($files, 'boost')) {
            $content[] = 'module_list.tpl file updated.';
        } else {
            $content[] = 'module_list.tpl could not be updated.';
        }
        $content[] = '
2.1.2 changes
--------------------
+ RFE #1720749 - Boost now detects and warns users of old module
  installs.
+ Boost now expects the javascript directory to be writable as
  well as the module directory.
+ Added inBranch function for modules
+ Added revert option to copy a module\'s files locally.
</pre>';
    }

    return TRUE;
}

?>