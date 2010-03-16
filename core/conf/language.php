<?php

/**
 * This config file allows you to set phpWebSite language settings
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * Should phpWebSite be unable to assign a language to a user
 * it will default to the one below. MAKE SURE you use one that
 * has been tested with setlocale or you will get English each
 * time.
 *
 * This setting used to be in core's config.php file but was moved
 * here. The below will not go into effect if already set. Do not remove
 * the defined check until you remove the define from your config/config.php
 * file.
 */

if (!defined('DEFAULT_LANGUAGE')) {
    define('DEFAULT_LANGUAGE', 'en_US');
}

/**
 *  If set to TRUE, phpWebSite will not try to translate anything.
 *  All content will display in it native language.
 */
define('DISABLE_TRANSLATION', false);


/**
 * If set to TRUE, phpWebSite will ALWAYS use the default language
 * no matter what the user settings.
 *
 * If your DEFAULT_LANGUAGE is not set correctly, you may have site
 * problems. Test before setting this option.
 */
define('FORCE_DEFAULT_LANGUAGE', false);

/**
 * If set to true, phpWebSite will ignore the browser language settings
 * but WILL obey the user cookie
 */
define('IGNORE_BROWSER_LANGUAGE', false);

/**
 * If true, phpWebSite will use the putenv function to set
 * LANGUAGE and LANG environment variables.
 * This may cause problems with some servers.
 * Default is "false"
 */
define('USE_PUTENV', false);

?>