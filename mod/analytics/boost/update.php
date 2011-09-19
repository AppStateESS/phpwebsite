<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

function analytics_update(&$content, $currentVersion)
{
    switch($currentVersion) {
        case version_compare($currentVersion, '1.0.1', '<'):
            $db = new PHPWS_DB('analytics_tracker');
            $result = $db->addTableColumn('disable_if_logged', 'int NOT NULL default 0');
            if(PHPWS_Error::logIfError($result)) {
                $content[] = 'Unable to add disable_if_logged column to analytics_tracker table.';
                return false;
            }

            $files = array('templates/edit.tpl');
            if(PHPWS_Boost::updateFiles($files, 'analytics')) {
                $content[] = '--- Updated templates/edit.tpl';
            }
    }

    return true;
}

?>
