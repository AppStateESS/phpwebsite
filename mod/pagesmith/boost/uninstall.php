<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function pagesmith_uninstall(&$content)
{
    Core\DB::dropTable('ps_block');
    Core\DB::dropTable('ps_text');
    Core\DB::dropTable('ps_page');
    $content[] = 'Tables removed.';
    return TRUE;
}

?>