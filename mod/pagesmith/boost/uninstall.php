<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function pagesmith_uninstall(&$content)
{
    \core\DB::dropTable('ps_block');
    \core\DB::dropTable('ps_text');
    \core\DB::dropTable('ps_page');
    $content[] = 'Tables removed.';
    return TRUE;
}

?>