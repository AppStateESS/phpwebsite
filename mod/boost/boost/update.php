<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function boost_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '1.6.1', '<'):
        $content[] = '+ Boost wasn\'t updating modules if there wasn\'t an update.php file.';
        $content[] = '+ Dependencies now checked during update.';
        break;

    case version_compare($currentVersion, '1.7.0', '<'):
        $content[] = '+ Adding dependency table for core.';
        $filename = PHPWS_SOURCE_DIR . 'mod/boost/boost/update_1_7_0.sql';
        $db = & new PHPWS_DB;
        return $db->importFile($filename);
        break;
    }
    return TRUE;
}

?>