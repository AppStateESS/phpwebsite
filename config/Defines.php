<?php

/**
 * Defines for Beanie. Setting here should rarely change.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
define('CONFIGURATION_FILE', './Config/Configuration.php');

/**
 * Location of module assets (Javascript, CSS, HTML, etc.)
 * This path must be publically accessible and on the web server path.
 */
define('SHARED_ASSETS', 'http://localhost/beanie-cms/');

define('ROOT_DIRECTORY', getcwd());

/**
 * Path to error log file directory.
 * Path must end with a (forward) slash
 * This directory must be writable by Apache.
 */
define('ERROR_LOG_DIRECTORY', 'logs/');

/**
 * If true, a table existence will be check prior to inserting it into a DB
 * object. The default is FALSE. Change to true for debugging ONLY.
 *
 */
define('DATABASE_CHECK_TABLE', true);

/**
 * If true, a column's existence will be check prior to inserting it into a DB
 * Table object. The default is FALSE. Change to true for debugging ONLY.
 */
define('DATABASE_CHECK_COLUMNS', true);

/**
 * If true, a transaction initializes a engine check of all involved tables. If
 * a table doesn't support transactions, an exception will be thrown. The
 * default is FALSE. Change to true for debugging.
 */
define('DATABASE_CHECK_ENGINE', true);

/**
 * The permission mode a log file is written as. Default is read-only.
 */
define('LOG_FILE_PERMISSION', 0200);

/**
 * See http://www.php.net/manual/en/timezones.php
 */
date_default_timezone_set('America/New_York');


/**
 * If true, the log will contain a stack trace for each error.
 * Setting this to false shows the message ONLY.
 */
define('LOG_ERROR_STACK', true);

/**
 * The default template to use in Global/Error/Pages/ when a error is reported.
 * @see Error::errorPage()
 */
define('ERROR_PAGE_TEMPLATE', 'default.html');

/**
 * Will display errors instead of error page.
 * Only change to true for debugging: never in production.
 */
define('DISPLAY_ERRORS', true);

/**
 * If true, errors produced by the system will be changed to exceptions. This should
 * be TRUE for production and some cases of debugging.
 * Changing it false is helpful for Xdebug (if installed) error reports.
 */
define('ERRORS_AS_EXCEPTION', false);

/**
 * Directory containing site configuration files.
 */
define('SITE_CONFIGURATION_DIRECTORY', 'Config/Sites/');

?>
