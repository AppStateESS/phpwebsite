<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rideboard_uninstall(&$content) {
    $content[] = dgettext('rideboard', 'Removing ride.');
    \core\Error::logIfError(core\DB::dropTable('rb_ride'));
    $content[] = dgettext('rideboard', 'Removing location table.');
    \core\Error::logIfError(core\DB::dropTable('rb_location'));

    return true;
}


?>