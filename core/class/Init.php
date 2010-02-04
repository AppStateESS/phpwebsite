<?php

/**
 * Init initializes phpWebSite. It does the following:
 * - sets the language settings and contains the 'translate function'
 * - starts most the vital core classes
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    define('PHPWS_SOURCE_DIR', str_replace('core/class', '', dirname(__FILE__)));
}

require_once PHPWS_SOURCE_DIR . 'core/conf/language.php';

if (!defined('PHPWS_HOME_DIR')) {
    define('PHPWS_HOME_DIR', './');
}

if (!defined('IGNORE_BROWSER_LANGUAGE')) {
    define('IGNORE_BROWSER_LANGUAGE', false);
}
initializei18n();
loadBrowserInformation();

/* Load the Core class */
require_once PHPWS_SOURCE_DIR . 'core/class/Core.php';

define('PHPWS_HOME_HTTP', PHPWS_Core::getHomeHttp());

/***** PHPWS Classes ****/

PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Time.php');
PHPWS_Core::initCoreClass('Settings.php');
PHPWS_Core::initCoreClass('Link.php');
PHPWS_Core::initCoreClass('Text.php');
PHPWS_Core::initCoreClass('Debug.php');
PHPWS_Core::initCoreClass('Error.php');
PHPWS_Core::initCoreClass('Cache.php');
PHPWS_Core::initCoreClass('Key.php');
PHPWS_Core::initCoreClass('Cookie.php');
PHPWS_Core::initCoreClass('Security.php');
PHPWS_Core::initCoreClass('Icon.php');

if (!defined('USE_ROOT_CONFIG')) {
    define('USE_ROOT_CONFIG', FALSE);
}

function initializei18n()
{
    /* Initialize language settings */
    if (DISABLE_TRANSLATION || !function_exists('bindtextdomain')) {
        define('CURRENT_LANGUAGE', 'en_US');
        define('PHPWS_TRANSLATION', FALSE);

        if (!function_exists('gettext')) {
            function gettext($mod, $text) {
                return $text;
            }
        }

        if (!function_exists('dgettext')) {
            function dgettext($mod, $text) {
                return $text;
            }
        }

        if (!function_exists('dngettext')) {
            function dngettext($mod, $text) {
                return $text;
            }
        }

        if (!function_exists('_')) {
            function _($text) {
                return $text;
            }
        }
    } else {
        define('PHPWS_TRANSLATION', TRUE);
        initLanguage();
        $core_locale = PHPWS_SOURCE_DIR . 'locale';

        bindtextdomain('core', $core_locale);
        textdomain('core');

        $handle = opendir(PHPWS_SOURCE_DIR . "mod/");

        while ($mod_name = readdir($handle)) {
            $localedir = sprintf('%smod/%s/locale/', PHPWS_SOURCE_DIR, $mod_name);

            if (is_dir(PHPWS_SOURCE_DIR . 'mod/' . $mod_name)) {
                if (file_exists($localedir) && $mod_name != "..") {
                    bindtextdomain($mod_name, $localedir);
                }
            }
        }
        closedir($handle);
    }
}

function setLanguage($language)
{
    // putenv may cause problems with safe_mode.
    // change USE_PUTENV in the language.php config file
    if (USE_PUTENV) {
        putenv("LANG=$language");
        putenv("LANGUAGE=$language");
    }

    $versions[] = $language . '.UTF-8';
    $versions[] = $language . '.UTF8';
    $versions[] = $language;

    return setlocale(LC_ALL, $versions);
}

/**
 * Initializes language
 * Be aware this is called BEFORE the Core class
 * is established.
 */
function initLanguage()
{
    if (!defined('DEFAULT_LANGUAGE')) {
        define('DEFAULT_LANGUAGE', 'en_US');
    }

    if (!defined('CORE_COOKIE_TIMEOUT')) {
        define('CORE_COOKIE_TIMEOUT', 3600);
    }

    // Language will ignore user settings if FORCE_DEFAULT_LANGUAGE is true
    // See language.php configuration file
    if (!FORCE_DEFAULT_LANGUAGE && isset($_COOKIE['phpws_default_language'])) {
        $language = $_COOKIE['phpws_default_language'];
        $locale = setLanguage($language);

        if ($locale == FALSE) {
            $locale = setLanguage(DEFAULT_LANGUAGE);
        }
        $locale = preg_replace('/\.utf8|\.utf-8/i', '', $locale);
    } else {
        $locale_found = FALSE;

        if (!FORCE_DEFAULT_LANGUAGE && !IGNORE_BROWSER_LANGUAGE) {
            $userLang = getBrowserLanguage();
            foreach ($userLang as $language) {
                if (strpos($language, '-')) {
                    $testslash =  explode('-', $language);
                    $test[0] = $testslash[0] . '_' . strtoupper($testslash[1]);
                }

                $test[1] = $language;
                $test[2] = substr($language, 0, 2);
                $test[3] = $test[2] . '_' . strtoupper($test[2]);

                foreach ($test as $langTest){
                    if (setLanguage($langTest)) {
                        $locale_found = TRUE;
                        $locale = $langTest;
                        setcookie('phpws_default_language', $locale, mktime() + CORE_COOKIE_TIMEOUT);
                        break;
                    }
                }

                if ($locale_found) {
                    break;
                }
            }
        }

        if ($locale_found == FALSE) {
            $locale = setLanguage(DEFAULT_LANGUAGE);
            setcookie('phpws_default_language', $locale, mktime() + CORE_COOKIE_TIMEOUT);
        }
    }

    if ($locale != FALSE) {
        define('CURRENT_LANGUAGE', $locale);
    }
    else {
        define('CURRENT_LANGUAGE', DEFAULT_LANGUAGE);
    }

    loadLanguageDefaults($locale);
}


function loadBrowserInformation()
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        $GLOBALS['browser'] = NULL;
        return;
    }

    $agent = & $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/msie/i', $agent)) {
        $browser = 'MSIE';
    } elseif (preg_match('/firefox/i', $agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/opera/i', $agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/safari/i', $agent)) {
        $browser = 'Safari';
    }

    $GLOBALS['browser'] = & $browser;
}


function getBrowserLanguage()
{
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return explode(',', preg_replace("/(;q=\d\.*\d*)/", '', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
    }
    else {
        return array(DEFAULT_LANGUAGE);
    }
}


function loadLanguageDefaults($language)
{
    $rootDir = PHPWS_SOURCE_DIR . 'core/conf/i18n/';
    if (is_file($rootDir . $language . '.php')){
        require_once $rootDir . $language . '.php';
    } else {
        $rootLanguage = explode('_', $language);
        if (is_file($rootDir . $rootLanguage . '_default.php')) {
            require_once $rootDir . $rootLanguage . '_default.php';
        } else {
            require_once $rootDir . 'default.php';
        }
    }
}

function doubleLanguage($language)
{
    return $language . '_' . strtoupper($language);
}


/**
 * adds a locale prefix to a title name
 */
function translateFile($filename)
{
    $language = str_ireplace('.utf-8', '', CURRENT_LANGUAGE);
    return strtolower($language . '_' . $filename);
}

/**
 * get information about user's browser
 *
 * browser
 * browser_version
 * engine
 * engine_version
 * locale
 */
function getBrowser()
{
    if (!isset($GLOBALS['browser'])) {
        return NULL;
    } else {
        return $GLOBALS['browser'];
    }
}

?>