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
        if (PHPWS_Boost::updateFiles($files, 'blog')) {
            $content[] = '+ Updated config.tpl template.';
        } else {
            $content[] = '+ Unable to copy updated config.tpl template.';
        }
        $content[] = '+ Updated locale files.
+ Added translate functions.
+ Removed language setting from default config.php template
+ Added missing cache_directory value for config.tpl.</pre>';
        
    }
    return true;
}

?>