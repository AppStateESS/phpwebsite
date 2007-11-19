<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rideboard_uninstall(&$content) {
    $content[] = dgettext('rideboard', 'Removing ride.');
    PHPWS_Error::logIfError(PHPWS_DB::dropTable('rb_ride'));
    $content[] = dgettext('rideboard', 'Removing location table.');
    PHPWS_Error::logIfError(PHPWS_DB::dropTable('rb_location'));

    return true;
}


?>