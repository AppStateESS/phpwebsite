<?php

/********************** CORE SETTINGS ***************************/
/**
 * The source directory tells the system where your installation
 * is. This is important to branches.
 */
define("PHPWS_SOURCE_DIR", "{source_dir}");

/**
 * Your site hash defines your site identity. It is used
 * to create cookies and name your session hash (see below).
 */
define("SITE_HASH", "{site_hash}");

/**
 * If you do not name your session, then branches or other
 * installations on the same server will get confused.
 * You can name your session whatever you wish, but basing
 * it on your site hash is not a bad idea.
 */
define("SESSION_NAME", md5(SITE_HASH));


/**
 * Some people are not blessed with the ability to just have
 * more than one database on their server. If you are one of
 * those people, you will need to attach a table prefix to 
 * each installation.
 */
define("TABLE_PREFIX", "{dbprefix}");

/**
 * This is you database information. The format is as follows:
 * "db_type://dbuser:dbpassword@dbhost/dbname"
 * This format must be exact.
 */ 
//define("PHPWS_DSN", "dbtype://dbuser:dbpass@localhost/dbname");
define("PHPWS_DSN", "{dsn}");


/**
 * The core will occasionally save cookies. They do not contain
 * important information. This is the time until they expire.
 */
define("CORE_COOKIE_TIMEOUT", 2592000);

/********************** Security Settings *********************/
/**
 * If CHECK_DIRECTORY_PERMISSIONS is TRUE the phpwebsite WILL
 * NOT let you run the site until the following directories are
 * made non writable.
 * config/
 * templates/
 * 
 * These directories are made writable during installations but
 * need not be at any other time.
 */

// FOR NOW, this will be FALSE. MUST BE TRUE on Release
define("CHECK_DIRECTORY_PERMISSIONS", FALSE);

/********************** Logging Settings **********************/

/**
 * Directory where logs will be written
 */
define("PHPWS_LOG_DIRECTORY", "./logs/");

/**
 * Determines the permissions given to log files when written
 * to the logs directory.
 * This MUST be 4 digits and without quotation marks.
 */
define("LOG_PERMISSION", 0644);

/**
 *  The time format for each log entry
 */
define("LOG_TIME_FORMAT", "%X %x");

/*********************** TEMPLATES *****************************/
/**
 * Setting FORCE_THEME_TEMPLATES to TRUE forces the template class
 * to ONLY look for template files in your current theme. When FALSE
 * the template class will first look in your theme then in the 
 * templates/ directory. When FALSE, the template class has to make
 * sure the file is in the theme. If you know for sure, it is then
 * setting this to TRUE will save a file check.
 */

define("FORCE_THEME_TEMPLATES", FALSE);

/**
 * phpWebSite uses templates from the templates directory by default.
 * This makes sense for ordering purposes and to make branches load
 * faster.
 * However if you are developing, you may not want it too. In that
 * case you can force the core to pull module templates from the
 * module template directory directly. Set the below to TRUE if
 * this is the case.
 */

define("FORCE_MOD_TEMPLATES", TRUE);

/************************ POST CHECK ***************************/
/**
 * Determines how many previous posts the session will store. If
 * isPosted is called and a previous post matches the list, the
 * developer can prevent back clicking and refresh problems.
 */
define("MAX_POST_TRACK", 10);

/*********************** CONFIG FILES **************************/
/**
 * By default, phpWebSite checks configuration files in the
 * config directory when getConfigFile is called. If you wish
 * core to only grab mod configuration files, set the below
 * to TRUE.
 */
define("FORCE_MOD_CONFIG", TRUE);

/********************** PEAR SETTINGS **************************/

/**
 * phpWebSite ships with a "known working version" of pear. This
 * means it works for us. You may decide to alter this depending
 * on how your server is setup. Do not edit it unless you
 * know what you are doing.
 */
// *nix / Linux environments
{LINUX_PEAR}ini_set("include_path", ".:" . PHPWS_SOURCE_DIR . "lib/pear/");

// Windows environments. Use this one instead on a windows machine.
{WINDOWS_PEAR}ini_set("include_path", ".;" . PHPWS_SOURCE_DIR . "lib\\pear\\");

/************************ LANGUAGE *****************************/
/**
 * Should phpWebSite be unable to assign a language to a user
 * it will default to the one below. MAKE SURE you use one that
 * has been tested with setlocale or you will get English each
 * time.
 */
define("DEFAULT_LANGUAGE", "en");

/************************ LIST ********************************/
/**
 * Assigns a css style to the toggle element in list
 */

define("PHPWS_LIST_TOGGLE_CLASS", " class=\"bg-light\"");



/*********************** ERROR DEFINITIONS *********************/
/**
 * You shouldn't ever have to alter these.
 */

/* Error logging presets */
define("PHPWS_LOG_ERRORS", TRUE);

?>
