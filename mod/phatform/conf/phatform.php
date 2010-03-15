<?php
/**
 * Configuration file for phatform module
 *
 * @version $Id$
 */

include(PHPWS_SOURCE_DIR . 'mod/phatform/boost/boost.php');

/* Phatform version number */
define('PHAT_VERSION', $version);

/* Main title to use throughout phatform */
define('PHAT_TITLE', 'Form Generator v' . PHAT_VERSION);

/* Set the hex to use for alternating section colors when viewing forms */
define('PHAT_SECTION_HEX', '#eeeeee');

/* Set default rows and columns for textareas */
define('PHAT_DEFAULT_ROWS', 5);
define('PHAT_DEFAULT_COLS', 40);

/* Set default size and maxsize for textfields */
define('PHAT_DEFAULT_SIZE', 33);
define('PHAT_DEFAULT_MAXSIZE', 255);

/* Whether or not the blurb and value are required fields when making a form */
define('PHAT_BLURB_REQUIRED', 0);
define('PHAT_VALUE_REQUIRED', 0);

/* Default size for a multiselect list */
define('PHAT_MULTISELECT_SIZE', 4);

/* Turn on and off debugging */
define('PHAT_DEBUG_MODE', 0);

/* Default page limit for form elements */
define('PHAT_PAGE_LIMIT', 10);

/* Turn on and off instructions */
define('PHAT_SHOW_INSTRUCTIONS', 1);

/* How many entries to show per page when viewing data */
define('PHAT_ENTRY_LIST_LIMIT', 20);

/* Time to live for the cache of the entry list */
define('PHAT_ENTRY_LIST_TTL', 300);

/* Maximum number of characters for a textarea */
define('PHAT_MAX_CHARS_TEXT_ENTRY', 3000);

/* Set outcoming mail preference.
 Available Options: text/plain, text/html
 */
define('PHAT_MAIL_CONTENT_TYPE', 'text/html');

/* Set to false disables captcha on anonymous pages
 */
define('PHATFORM_CAPTCHA', true);

?>