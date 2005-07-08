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
    }
    return TRUE;
}

?>