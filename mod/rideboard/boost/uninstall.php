<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rideboard_uninstall(&$content) {
    $content[] = dgettext('rideboard', 'Removing driver table.');
    PHPWS_Error::logIfError(PHPWS_DB::dropTable('rb_driver'));
    $content[] = dgettext('rideboard', 'Removing passenger table.');
    PHPWS_Error::logIfError(PHPWS_DB::dropTable('rb_passenger'));
    $content[] = dgettext('rideboard', 'Removing location table.');
    PHPWS_Error::logIfError(PHPWS_DB::dropTable('rb_location'));

    return true;
}


?>