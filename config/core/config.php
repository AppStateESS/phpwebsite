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
define("SITE_HASH", "864e5c6d034fea29b98e6fbbd9b5707c");

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
define("PHPWS_DSN", "dbtype://dbuser:dbpass@localhost/dbname");


/**
 * The core will occasionally save cookies. They do not contain
 * important information. This is the time until they expire.
 */
define("CORE_COOKIE_TIMEOUT", 2592000);

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

/*************************** FORM ****************************/
/* These are the default values for various form elements */
// default number of rows in a textarea
define("DFLT_ROWS", 5);

//default number of cols in a textarea
define("DFLT_COLS", 40);

//default size of text field spaces
define("DFLT_TEXT_SIZE", 20);

// default max character limit of text fields
define("DFLT_MAX_SIZE", 255);

// default number of rows to show in a multiple select
define("DFLT_MAX_SELECT", 4);

// max width in pixels allowed for uploaded images
define("MAX_IMAGE_WIDTH", 640);

// max height in pixels allowed for uploaded images
define("MAX_IMAGE_HEIGHT", 480);

// max image size in kilobytes allowed for uploaded images
define("MAX_IMAGE_SIZE", 80000);


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
define("PHPWS_DIR_NOT_WRITABLE",      -4);
define("PHPWS_VAR_TYPE",              -5);
define("PHPWS_STRICT_TEXT",           -6);
define("PHPWS_INVALID_VALUE",         -7);


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

/************* Form.php *****************/
define("PHPWS_FORM_BAD_NAME",         -301);
define("PHPWS_FORM_MISSING_NAME",     -302);
define("PHPWS_FORM_WRONG_ELMT_TYPE",  -303);
define("PHPWS_FORM_NAME_IN_USE",      -304);
define("PHPWS_FORM_MISSING_TYPE",     -305);
define("PHPWS_FORM_NO_ELEMENTS",      -306);
define("PHPWS_FORM_NO_TEMPLATE",      -307);
define("PHPWS_FORM_NO_FILE",          -308);
define("PHPWS_FORM_IMG_TOO_BIG",      -309);
define("PHPWS_FORM_WIDTH_TOO_BIG",    -310);
define("PHPWS_FORM_HEIGHT_TOO_BIG",   -311);
define("PHPWS_FORM_UNKNOWN_TYPE",     -312);
define("PHPWS_FORM_INVALID_MATCH",    -313);

define("PHPWS_FORM_ERROR_FILE_POST",  -320);


/*************** Item.php *****************/
define("PHPWS_ITEM_ID_TABLE",           -400);
define("PHPWS_ITEM_NO_RESULT",          -401);

?>