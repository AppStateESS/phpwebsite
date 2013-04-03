<?php

/**
 * The Backward compatibility defines file. These setting apply ONLY to old
 * modules
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
ini_set('include_path', '.:Global/Backward/pear/');

define('MAX_POST_TRACK', 10);

/******************* ABSOLUTE LIMIT ***************************/
/**
 * This the absolute upload limit in bytes. No matter what the code or
 * module says the user can upload, this amount, if checked, will trump
 * it.
 * This should be set reasonably high. The default is ~5mb
 */
define('ABSOLUTE_UPLOAD_LIMIT', '15000000');

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

/*********************** TEMPLATES *****************************/
/**
 * Setting FORCE_THEME_TEMPLATES to TRUE forces the template class
 * to ONLY look for template files in your current theme. When FALSE
 * the template class will first look in your theme then in the
 * templates/ directory. When FALSE, the template class has to make
 * sure the file is in the theme. If you know for sure, it is then
 * setting this to TRUE will save a file check.
 */

define('FORCE_THEME_TEMPLATES', false);

/**
 * Normally, if the the Pear template class can't fill in at least one
 * tag in a template, it will return NULL. Setting the below to TRUE,
 * causes the phpWebSite to still return template WITHOUT the tag
 * substitutions. This should normally be set to FALSE unless you are
 * testing code.
 */

define('RETURN_BLANK_TEMPLATES', true);

/**
 * If you want template to prefix the templates it is using with an
 * information tag, set the below to TRUE.
 * DO NOT leave this set to TRUE or use this on a live server
 * as it reveals your installation path.
 */

define('LABEL_TEMPLATES', false);
?>
