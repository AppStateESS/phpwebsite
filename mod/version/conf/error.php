<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$errors = array(
VERSION_MISSING_ID      => dgettext('version', 'Version function requires an item id.'),
VERSION_NO_TABLE        => dgettext('version', 'Source table not found.'),
VERSION_NOT_MODULE      => dgettext('version', 'Unknown module'),
VERSION_WRONG_SET_VAR   => dgettext('version', 'Set variable must be an object or an array.'),
VERSION_MISSING_SOURCE  => dgettext('version', 'Missing source data.'),
VERSION_DEFAULT_MISSING => dgettext('version', 'Missing columns.php file')
);
?>