<?php

/********************** CORE SETTINGS ***************************/
/**
 * The source directory tells the system where your installation
 * is. This is important to branches.
 */
define('PHPWS_SOURCE_DIR', '{source_dir}');
define('PHPWS_HOME_DIR', '{home_dir}');

/**
 * Your site hash defines your site identity. It is used
 * to create cookies and name your session hash (see below).
 */
define('SITE_HASH', '{site_hash}');

/**
 * This is you database information. The format is as follows:
 * 'db_type://dbuser:dbpassword@dbhost/dbname'
 * This format must be exact.
 */
//define('PHPWS_DSN', 'dbtype://dbuser:dbpass@localhost/dbname');
define('PHPWS_DSN', '{dsn}');


<!-- BEGIN pre -->
/**
 * Defining the below will prefix each phpwebsite table with the
 * chosen characters.
 */
define('PHPWS_TABLE_PREFIX', '{dbprefix}');
<!-- END pre -->

/**
 * Added 11/16/2006
 * If your provider does not allow you to create indexes then
 * change this value to 'false' while you look for a new provider.
 */
define('DB_ALLOW_TABLE_INDEX', true);


/**
 * Some modules may try to lock tables when processing the database.
 * Some admins don't want to allow LOCK permissions, in which case
 * you can set the ALLOW_TABLE_LOCKS define to false. If you have
 * a large site, you know a module uses locks, and you have the permission
 * set the below to true.
 */
define('ALLOW_TABLE_LOCKS', false);


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


/********************** Security Settings *********************/
/**
 * If CHECK_DIRECTORY_PERMISSIONS is TRUE the phpwebsite WILL
 * NOT let you run the site until the following directories are
 * made non writable.
 * config/
 * templates/
 *
 * These directories are made writable during installations and
 * updates but need not be at any other time.
 *
 * This can become a headache but enabling it will increase security
 */

define('CHECK_DIRECTORY_PERMISSIONS', FALSE);

/************************ Captcha Settings *******************/
/**
 * Added 11/13/2006
 * Determines if you want to use captcha and if so which version.
 * phpWebSite ships with freecap by default which is supplied by
 * http://www.puremango.co.uk/
 *
 */

define('ALLOW_CAPTCHA', true);
define('CAPTCHA_NAME', 'freecap');


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



/********************** Logging Settings **********************/

/**
 * Error logging presets
 * If you cannot secure your log directory, this should be changed
 * to FALSE
 */
define('PHPWS_LOG_ERRORS', TRUE);


/**
 * Directory where logs will be written
 */
define('PHPWS_LOG_DIRECTORY', 'logs/');

/**
 * Determines the permissions given to log files when written
 * to the logs directory.
 * This MUST be 4 digits and without quotation marks.
 */
define('LOG_PERMISSION', 0600);

/**
 *  The time format for each log entry
 */
define('LOG_TIME_FORMAT', '%X %x');

/************************ POST CHECK ***************************/
/**
 * Determines how many previous posts the session will store. If
 * isPosted is called and a previous post matches the list, the
 * developer can prevent back clicking and refresh problems.
 */
define('MAX_POST_TRACK', 10);

/*********************** CONFIG FILES **************************/
/**
 * By default, phpWebSite checks configuration files in the
 * config directory when getConfigFile is called. If you wish
 * core to only grab mod configuration files, set the below
 * to TRUE.
 */
define('FORCE_MOD_CONFIG', FALSE);

/********************** PEAR SETTINGS **************************/

/**
 * phpWebSite ships with a 'known working version' of pear. This
 * means it works for us. You may decide to alter this depending
 * on how your server is setup. Do not edit it unless you
 * know what you are doing.
 */
// *nix / Linux environments
{LINUX_PEAR}ini_set('include_path', '.:' . PHPWS_SOURCE_DIR . 'lib/pear/');

// Windows environments. Use this one instead on a windows machine.
{WINDOWS_PEAR}ini_set('include_path', '.;' . PHPWS_SOURCE_DIR . 'lib\\pear\\');


/************************ LIST ********************************/
/**
 * Assigns a css style to the toggle element in list
 */

define('PHPWS_LIST_TOGGLE_CLASS', ' class="bgcolor1"');

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
define('DEFAULT_EDITOR_TOOL', 'fckeditor');
define('FORCE_EDITOR', true);

/******************* ABSOLUTE LIMIT ***************************/
/**
 * This the absolute upload limit in bytes. No matter what the code or
 * module says the user can upload, this amount, if checked, will trump
 * it.
 * This should be set reasonably high. The default is ~5mb
 */
define('ABSOLUTE_UPLOAD_LIMIT', '5000000');


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
define('CACHE_DIRECTORY', '{cache_directory}');

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


/********************** JQuery latest ***************************/
/**
 * If true, the jquery call will use the script directly from the
 * home site. Otherwise, a local copy will be used.
 */
define('JQUERY_LATEST', false);
?>
