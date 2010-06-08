<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

function pulse_update(&$content, $currentVersion)
{
    switch($currentVersion) {

        case version_compare($currentVersion, '1.9.1', '<'):
            if(Core\Error::logIfError(Core\DB::dropTable('pulse_schedule'))) {
                $content[] = 'Could not drop pulse_schedule table.';
                return;
            }
            $result = Core\DB::importFile(PHPWS_SOURCE_DIR . 'mod/pulse/boost/install.sql');
            if(Core\Error::logIfError($result)) {
                $content[] = 'Could not run install.sql.';
                return;
            }
            $content[] = '<pre>Replaced pulse_schedule table</pre>';
            break;
    }

    return TRUE;
}

?>
