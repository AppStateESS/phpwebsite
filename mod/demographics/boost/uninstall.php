<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function demographics_uninstall(&$content)
{
    Core\DB::dropTable('demographics');
    $content[] = dgettext('demographics', 'Demographics table removed.');
    return TRUE;
}

?>