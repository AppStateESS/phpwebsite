<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function demographics_uninstall(&$content)
{
    PHPWS_DB::dropTable('demographics');
    $content[] = dgettext('demographics', 'Demographics table removed.');
    return TRUE;
}

?>