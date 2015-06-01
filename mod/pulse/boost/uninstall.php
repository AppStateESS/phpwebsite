<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function pulse_uninstall(&$content)
{
    $content[] = 'Dropping schedule table.';
    $db = Database::newDB();
    $t1 = $db->addTable('pulse_schedule');
    $t1->drop();
    return true;
}
