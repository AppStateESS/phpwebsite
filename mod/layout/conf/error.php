<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$errors = array(
LAYOUT_SESSION_NOT_SET   => dgettext('layout', 'Layout session not set'),
LAYOUT_NO_CONTENT        => dgettext('layout', 'Layout did not receive any data for display'),
LAYOUT_NO_THEME          => dgettext('layout', 'Unable to receive theme information.'),
LAYOUT_BAD_JS_DATA       => dgettext('layout', 'Data was not an array.'),
LAYOUT_JS_FILE_NOT_FOUND => dgettext('layout', 'Javascript file was not found.'),
LAYOUT_BOX_ORDER_BROKEN  => dgettext('layout', 'Box order is out of sequence.'),
LAYOUT_INI_FILE          => dgettext('layout', 'The theme.ini file is missing for the default theme.'),
LAYOUT_BAD_THEME_VAR     => dgettext('layout', 'An instruction was requested on a missing theme variable.')
);

?>