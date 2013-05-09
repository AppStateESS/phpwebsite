<?php


/**
 * The default template to use in Global/Error/Pages/ when a error is reported.
 * @see Error::errorPage()
 */
define('ERROR_PAGE_TEMPLATE', 'default.html');


/**
 * Error settings
 */

/**
 * Will display errors instead of error page.
 * Only change to true for debugging: never in production.
 * If this is FALSE, ERRORS_AS_EXCEPTION should be TRUE to get a proper
 * error page.
 * Default: FALSE
 */
define('DISPLAY_ERRORS', TRUE);

/**
 * If TRUE and DISPLAY_ERRORS and ERRORS_AS_EXCEPTION are TRUE, PHP warnings
 * and notices will be thrown as exceptions.
 * Note: these notices may not be seen with ERRORS_AS_EXCEPTION as FALSE.
 * Default: FALSE.
 */
define('SHOW_ALL_ERRORS', TRUE);

/**
 * If true, errors produced by the system will be changed to exceptions. This should
 * be TRUE for production and some cases of debugging.
 * Changing it FALSE is helpful for Xdebug (if installed) error reports.
 * Default: TRUE
 */
define('ERRORS_AS_EXCEPTION', TRUE);


/**************************************************************
 * The settings in this file affect the hub and all branches
 * Most of the defines from this file were originally in the
 * config.php file. That file now contains hub/branch specific
 * information.
 *
 * PLEASE NOTE: WE DO NOT RECOMMEND EDITING THIS FILE.  If you
 * need to change it, copy it first to core/conf/defines.php,
 * and that file will be loaded instead.  Otherwise, next time
 * you update phpWebSite, this file will be replaced and your
 * changes lost.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

/***************** Database ***********************************/

/**
 * If true, a table existence will be check prior to inserting it into a DB
 * object. The default is FALSE. Change to true for debugging ONLY.
 * > 1.8.0
 */
define('DATABASE_CHECK_TABLE', FALSE);

/**
 * If true, a column's existence will be check prior to inserting it into a DB
 * Table object. The default is FALSE. Change to true for debugging ONLY.
 * > 1.8.0
 */
define('DATABASE_CHECK_COLUMNS', FALSE);

/**
 * If true, a transaction initializes a engine check of all involved tables. If
 * a table doesn't support transactions, an exception will be thrown. The
 * default is FALSE. Change to true for debugging.
 * > 1.8.0
 */
define('DATABASE_CHECK_ENGINE', FALSE);


/************************ Cookie *******************************/
/**
 * The core will occasionally save cookies. They do not contain
 * important information. This is the time until they expire.
 */
define('CORE_COOKIE_TIMEOUT', 2592000);


/************************ Memory Setting **********************/
/* There is a chance that your installation may require more
 * memory than is alloted in your php.ini file. If ini_set
 * is allowed, you can uncomment and set the line before to
 * expand the memory capacity.
 *
 */
//ini_set('memory_limit', '10M');


/************************ Time Zone **************************/
/**
 * If SERVER_TIME_ZONE is commented out, phpWebSite will use the
 * server's default time zone (recommended). If you wish to force
 * a server timezone, uncomment the line and add the appropriate
 * setting -12 to 14. Half hours should be set with decimals (e.g.
 * 3:30 = 3.5
 *
 * if SERVER_TIME_ZONE is set then SERVER_USE_DST indicates whether
 * your server uses Daylight Savings Time.
 * Set to 1 for yes, or 0 for now. Commenting it out
 * sets it to zero
 */

//define('SERVER_TIME_ZONE', -4);
//define('SERVER_USE_DST', 1);

/**
 * PHP 5.1 requires a time zone to be set before you can use the
 * date function. The full list is here:
 * http://us3.php.net/manual/en/timezones.php
 */

define('DATE_SET_SERVER_TIME_ZONE', 'America/New_York');

/********************** Logging Settings **********************/

/**
 * If true, the log will contain a stack trace for each error.
 * Setting this to false shows the message ONLY.
 */
define('LOG_ERROR_STACK', FALSE);

/**
 * Directory where logs will be written
 */
define('LOG_DIRECTORY', PHPWS_SOURCE_DIR . 'logs/');

/**
 * Determines the permissions given to log files when written
 * to the logs directory.
 * This MUST be 4 digits and without quotation marks.
 */
define('LOG_PERMISSION', 0600);

/**
 *  The time format for each log entry
 */
define('LOG_TIME_FORMAT', '%Y-%m-%d %H:%M');

/************************ POST CHECK ***************************/
/**
 * Determines how many previous posts the session will store. If
 * isPosted is called and a previous post matches the list, the
 * developer can prevent back clicking and refresh problems.
 */
define('MAX_POST_TRACK', 10);


/********************** PEAR SETTINGS **************************/

/**
 * phpWebSite ships with a 'known working version' of pear. This
 * means it works for us. You may decide to alter this depending
 * on how your server is setup. Do not edit it unless you
 * know what you are doing.
 */
// *nix / Linux environments
ini_set('include_path', '.:' . PHPWS_SOURCE_DIR . 'lib/pear/');

// Windows environments. Use this one instead on a windows machine.
//ini_set('include_path', '.;' . PHPWS_SOURCE_DIR . 'lib\\pear\\');


/******************* ABSOLUTE LIMIT ***************************/
/**
 * This the absolute upload limit in bytes. No matter what the code or
 * module says the user can upload, this amount, if checked, will trump
 * it.
 * This should be set reasonably high. The default is ~5mb
 */
define('ABSOLUTE_UPLOAD_LIMIT', '15000000');


/******************** CACHING **********************************/
/**
 * There are two forms of caching in phpWebSite, both developed by
 * the PEAR team:
 * 1) Cache_Lite - a data caching method
 * 2) Sigma      - a template caching method
 *
 * Sigma is TRUE by default. Cache_Lite is FALSE by default.
 * You may or may not notice a speed increase. It is up to you
 * to decide.
 *
 * The CACHE_LIFETIME decides how many seconds between cache
 * updates. You will need to decide what is the optimal number.
 * The default is 3600 seconds (1 hour)
 *
 * If CACHE_TPL_LOCALLY is true (the default), template caching
 * will be stored in the installation's templates/cache directory.
 * If false, the caching will be stored in the CACHE_DIRECTORY
 * setting.
 *
 * The cache directory MUST BE WRITABLE by the webserver or it will
 * not work. Read the line above one more time. Make sure the
 * CACHE_DIRECTORY is set to writable directory. PHP normally uses
 * /tmp/. The directory MUST have a forward slash (/) on the end.
 */

define('ALLOW_CACHE_LITE', TRUE);
define('ALLOW_SIGMA_CACHE', TRUE);
define('CACHE_LIFETIME', 3600);
define('CACHE_TPL_LOCALLY', TRUE);
define('CACHE_DIRECTORY', '/tmp/');

/******************** MOD_REWRITE *******************************/
/**
 * Mod_rewrite is an Apache web server process that allows you to
 * reduce the size of your web urls. It must be enabled for it to
 * function properly.
 */

define('MOD_REWRITE_ENABLED', TRUE);

/******************* UTF8 Mode *********************************/
/**
 * Some core functions perform regular expressions matches using the \pL
 * parameter. Some versions of php don't support this. If you are getting
 * error messages with preg functions or dbpager search is not functioning,
 * try changing this value to FALSE. Some accent character support -may-
 * be lost as a result. You may also change this to false if your site
 * is native English.
 */

define ('UTF8_MODE', false);

/**--------------------------------------------------------------------------
 *  The settings below will be moved a settings module on the next release
 *--------------------------------------------------------------------------*/

/************************ EDITOR *******************************/
/*
 * If you have downloaded a wysiwyg editor or editors for
 * phpwebsite, you may enable their use below. You can also
 * choose which editor you want to use as the default.
 *
 * Example: define('DEFAULT_EDITOR_TOOL', 'fckeditor');
 *
 * Force editor is true by default to assure all choices. Changing
 * it to false will cause phpws to check your browser against the
 * editors supported.php file.
 */
define('USE_WYSIWYG_EDITOR', true);
define('DEFAULT_EDITOR_TOOL', 'ckeditor');
define('FORCE_EDITOR', true);

/************************ Captcha Settings *******************/
/**
* Determines if you want to use captcha and if so which version.
* phpWebSite ships with freecap by default which is supplied by
* http://www.puremango.co.uk/
*/
define('ALLOW_CAPTCHA', true);
define('CAPTCHA_NAME', 'freecap');

/**
 * If true, <script> tags can be submitted by a user who has scripting permissions.
 * Generally, leaving this false is the best course.
 */
define('ALLOW_SCRIPT_TAGS', false);

/************************ Cosign Settings *******************/
/**
 * If using Cosign for user authentication, please uncomment and
 * provide the settings below.  Note that you will also need to
 * configure your web server to provide Cosign protection on
 * /login relative to your website.  For example, in Apache:
 *
 * <Location "/mysite/login">
 *     CosignAllowPublicAccess off
 *
 *     RewriteEngine on
 *     RewriteRule .* /mysite [R=302]
 *  </Location>
 */
//define('COSIGN_LOGOUT_URL', 'http://cosign.example.com/cosign-bin/logout');

?>
