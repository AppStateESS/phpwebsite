<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function pulse_install(&$content)
{
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

    $content[] = 'Tables created';
    return true;
}