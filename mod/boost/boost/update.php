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

    case version_compare($currentVersion, '1.7.0', '<'):
        $content[] = '+ Adding dependency table for core.';
        $filename = PHPWS_SOURCE_DIR . 'mod/boost/boost/update_1_7_0.sql';
        $db = & new PHPWS_DB;
        return $db->importFile($filename);

    case version_compare($currentVersion, '1.7.1', '<'):
        $content[] = 'Add converted table.';
        $content[] = 'Add Key registration table.';
        $filename = PHPWS_SOURCE_DIR . 'mod/boost/boost/update_1_7_1.sql';
        $db = & new PHPWS_DB;
        return $db->importFile($filename);

    case version_compare($currentVersion, '1.8.0', '<'):
        $content[] = 'Added error messages.';
        $result = PHPWS_Boost::updateFiles(array('conf/error.php'), 'boost');
        if (!$result) {
            $content[] = _('Failed to update local files.');
            return FALSE;
        } elseif (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('Failed to update local files. Check your log.');
        } else {
            $content[] = _('Files updated successfully.');
        }
    }

    return TRUE;
}

?>