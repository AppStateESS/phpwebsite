<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
function pulse_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

        case version_compare($currentVersion, '1.9.1', '<'):
            if (PHPWS_Error::logIfError(PHPWS_DB::dropTable('pulse_schedule'))) {
                $content[] = 'Could not drop pulse_schedule table.';
                return;
            }
            $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/pulse/boost/install.sql');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = 'Could not run install.sql.';
                return;
            }
            $content[] = '<pre>Replaced pulse_schedule table</pre>';

        case version_compare($currentVersion, '2.0.0', '<'):
            if (PHPWS_Error::logIfError(PHPWS_DB::dropTable('pulse_schedule'))) {
                $content[] = 'Could not drop pulse_schedule table.';
                return;
            }

            $db = Database::newDB();
            $db->begin();

            try {
                $pulse = new \pulse\PulseSchedule;
                $st = $pulse->createTable($db);
            } catch (\Exception $e) {
                $error_query = $pulse->createQuery();
                if (isset($st) && $db->tableExists($st->getName())) {
                    $st->drop();
                }
                $content[] = 'Query:' . $error_query;
                $db->rollback();
                throw $e;
            }
            $db->commit();

            $content[] = 'New Pulse Schedule table created. ALL OLD SCHEDULES HAVE BEEN REMOVED!';
    }

    return TRUE;
}

?>
