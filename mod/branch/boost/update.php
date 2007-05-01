<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function branch_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '1.0.3', '<'):
        $content[] = 'This package does not update versions prior to 1.0.3.';
        return false;

    case version_compare($version, '1.0.4', '<'):
        $files = array('templates/config.tpl');
        $content[] = '<pre>1.0.4 Changes
-------------';
        if (PHPWS_Boost::updateFiles($files, 'branch')) {
            $content[] = '+ Updated config.tpl template.';
        } else {
            $content[] = '+ Unable to copy updated config.tpl template.';
        }
        $content[] = '+ Updated locale files.
+ Added translate functions.
+ Removed language setting from default config.php template
+ Added missing cache_directory value for config.tpl.</pre>';

    case version_compare($version, '1.0.5', '<'):
        $content[] = '<pre>1.0.5 Changes
-------------';
        $files = array('img/branch.png', 'templates/config.tpl');
        if (PHPWS_Boost::updateFiles($files, 'branch')) {
            $content[] = '+ Updated the following files:';
        } else {
            $content[] = '+ Failed to update the following files:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
+ Previous update had a typo in the file update: fixed.
+ Branch now copies htaccess file to branch site.
+ Trying different method of getting branch dsn.
+ Changed control panel icon</pre>';

    case version_compare($version, '1.1.0', '<'):
        PHPWS_Boost::updateFiles(array('templates/config.tpl'), 'branch');
        $content[] = '<pre>1.1.0 Changes
-------------
+ Conforms with all new Core changes.
+ Changed to new language format.
+ Added meta routing to core module installation section.
</pre>';
    }
    return true;
}

?>