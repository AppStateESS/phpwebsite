<?php

/********************** CORE SETTINGS ***************************/
/**
 * The source directory tells the system where your installation
 * is. This is important to branches.
 */
define("PHPWS_SOURCE_DIR", "/var/www/html/phpwebsite094/");

/**
 * Your site hash defines your site identity. It is used
 * to create cookies and name your session hash (see below).
 */
define("SITE_HASH", "864e5c6d0332598e6fbbd9b5707c");

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
define("TABLE_PREFIX", "");

/**
 * This is you database information. The format is as follows:
 * "db_type://dbuser:dbpassword@dbhost/dbname"
 * This format must be exact.
 */ 
define("PHPWS_DSN", "mysql://dbuser:dbpass@localhost/phpwebsite");

/**
 * The core will occasionally save cookies. They do not contain
 * important information. This is the time until they expire.
 */
define("CORE_COOKIE_TIMEOUT", 2592000);

/********************** PEAR SETTINGS **************************/

/**
 * phpWebSite ships with a "known working version" of pear. This
 * means it works for us. You may decide to alter this depending
 * on how your server is setup. Do not edit it unless you
 * know what you are doing.
 */
// *nix / Linux environments
ini_set("include_path", ".:" . PHPWS_SOURCE_DIR . "lib/pear/");

// Windows environments. Use this one instead on a windows machine.
//ini_set("include_path", ".;".PHPWS_SOURCE_DIR."lib\\pear\\");


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
define("PHPWS_LOG_DIRECTORY", "./logs/");


/* Error defines */

/************* Generic  *****************/
define("PHPWS_UNKNOWN",               -1);
define("PHPWS_FILE_NOT_FOUND",        -2);
define("PHPWS_CLASS_NOT_EXIST",       -3);

/*********** Database.php ***************/

define("PHPWS_DB_ERROR_TABLE",        -100);
define("PHPWS_DB_NO_VALUES",          -101);

/************* List.php *****************/
define("PHPWS_LIST_MOD_NOT_SET",      -200);
define("PHPWS_LIST_CLASS_NOT_SET",    -201);
define("PHPWS_LIST_TABLE_NOT_SET",    -202);
define("PHPWS_LIST_COLUMNS_NOT_SET",  -203);
define("PHPWS_LIST_NAME_NOT_SET",     -204);
define("PHPWS_LIST_OP_NOT_SET",       -205);
define("PHPWS_LIST_NO_ITEMS_PASSED",  -206);

?>