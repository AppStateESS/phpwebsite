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

    case version_compare($currentVersion, '2.1.3', '<'):
        $content[] = '<pre>';
        $content[] = '2.1.3 changes
---------------------
+ Minor update - Boost copies javascript directory properly.</pre>';

    case version_compare($currentVersion, '2.2.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/module_list.tpl');
        update_boost_files($files, $content);
        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/boost/boost/changes/2_2_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '2.2.1', '<'):
        $content[] = '<pre>
2.2.1 changes
----------------
+ Install, uninstall, and update use popups now.</pre>';

    case version_compare($currentVersion, '2.2.2', '<'):
        $content[] = '<pre>
2.2.2 changes
----------------
+ Added error log to copy directory command.</pre>';

    case version_compare($currentVersion, '2.3.0', '<'):
        if (!PHPWS_Boost::inBranch()) {
            $content[] = '<pre>';
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/boost/boost/changes/2_3_0.txt');
            $content[] = '</pre>';
        }
    }

    return TRUE;
}

function update_boost_files($files, &$content) {
    if (PHPWS_Boost::updateFiles($files, 'boost')) {
        $content[] = '--- The following files were updated successfully:';
    } else {
        $content[] = '--- Failed to update the following files:';
    }

    $content[] = '    ' . implode("\n    ", $files);
    $content[] = '';
}    

?>