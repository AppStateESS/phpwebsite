<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

translate('layout');
$errors = array(
		LAYOUT_SESSION_NOT_SET   => _('Layout session not set'),
		LAYOUT_NO_CONTENT        => _('Layout did not receive any data for display'),
		LAYOUT_NO_THEME          => _('Unable to receive theme information.'),
		LAYOUT_BAD_JS_DATA       => _('Data was not an array.'),
		LAYOUT_JS_FILE_NOT_FOUND => _('Javascript file was not found.'),
                LAYOUT_BOX_ORDER_BROKEN  => _('Box order is out of sequence.'),
                LAYOUT_INI_FILE          => _('The theme.ini file is missing for the default theme.')
translate();
);


?>