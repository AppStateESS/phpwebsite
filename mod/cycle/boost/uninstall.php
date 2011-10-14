<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

function cycle_uninstall(&$content)
{
    PHPWS_DB::dropTable('cycle_slots');
    $content[] = 'Cycle removed';
    
    return TRUE;
}
?>
