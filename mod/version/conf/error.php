<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

translate('version');

$errors = array(
		VERSION_MISSING_ID      => _('Version function requires an item id.'),
		VERSION_NO_TABLE        => _('Source table not found.'),
		VERSION_NOT_MODULE      => _('Unknown module'),
		VERSION_WRONG_SET_VAR   => _('Set variable must be an object or an array.'),
		VERSION_MISSING_SOURCE  => _('Missing source data.'),
                VERSION_DEFAULT_MISSING => _('Missing columns.php file')
		);

?>