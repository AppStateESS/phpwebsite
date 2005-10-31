<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function version_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.3', '<'):
        $content[] = '+ Version now removes version tables if the module is using an uninstall.sql file.';
        break;

    case version_compare($currentVersion, '0.0.4', '<'):
        PHPWS_Boost::registerMyModule('version', 'users', $content);
        PHPWS_Boost::registerMyModule('version', 'controlpanel', $content);
        $content[] = '+ Added admin panel link (may need to log out and in)';
        break;
    }
    return TRUE;
}

?>
