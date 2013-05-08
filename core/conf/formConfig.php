<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

/*************************** FORM ****************************/
/* These are the default values for various form elements */

// default number of rows in a textarea
define('DFLT_ROWS', 10);

//default number of cols in a textarea
define('DFLT_COLS', 40);

define('USE_DEFAULT_SIZES', FALSE);

//default size of text field spaces
define('DFLT_TEXT_SIZE', 40);

// default max character limit of text fields
define('DFLT_MAX_SIZE', 255);

// default number of rows to show in a multiple select
define('DFLT_MAX_SELECT', 4);

// To comply with XHTML, fieldsets are set to forms
define('FORM_DEFAULT_FIELDSET', FALSE);
define('FORM_GENERIC_LEGEND', _('Form'));

/**
 * If this is turned on, forms will add MAX_FILE_SIZE
 * restrictions as a hidden variable. This can prevent
 * large uploads. HOWEVER, if a file goes above the
 * max size, you will receive a system warning. So, you will
 * need to make sure display_errors is Off.
 */
define('FORM_USE_FILE_RESTRICTIONS', TRUE);

// Form will use the below only if ABSOLUTE_UPLOAD_LIMIT is not defined
define('FORM_MAX_FILE_SIZE', 15000000);



?>