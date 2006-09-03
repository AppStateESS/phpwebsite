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
    define('PHPWS_SOURCE_DIR', './');
}

if (!defined('PHPWS_HOME_DIR')) {
    define('PHPWS_HOME_DIR', './');
}

/* Initialize language settings */
if (!function_exists('bindtextdomain')){
    define('PHPWS_TRANSLATION', FALSE);
    function _($text) {
        return $text;
    }
} else {
    define('PHPWS_TRANSLATION', TRUE);
    initLanguage();
    translate('core');
    textdomain('messages');
}

loadBrowserInformation();

/* Load the Core class */
require_once PHPWS_SOURCE_DIR . 'core/class/Core.php';
require_once PHPWS_SOURCE_DIR . 'config/core/errorDefines.php';

/***** PHPWS Classes ****/

PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Time.php');
PHPWS_Core::initCoreClass('Settings.php');
PHPWS_Core::initCoreClass('Text.php');
PHPWS_Core::initCoreClass('Item.php');
PHPWS_Core::initCoreClass('Debug.php');
PHPWS_Core::initCoreClass('Error.php');
PHPWS_Core::initCoreClass('Cache.php');
PHPWS_Core::initCoreClass('Key.php');
PHPWS_Core::initCoreClass('Cookie.php');
PHPWS_Core::initCoreClass('Security.php');

if (!defined('USE_ROOT_CONFIG')) {
    define('USE_ROOT_CONFIG', FALSE);
}

function setLanguage($language)
{
    putenv("LANG=$language");
    putenv("LANGUAGE=$language");
    return setlocale(LC_ALL, $language);
}

/**
 * Initializes language 
 * Be aware this is called BEFORE the Core class
 * is established.
 */
function initLanguage()
{
    if (!defined('DEFAULT_LANGUAGE')) {
        define('DEFAULT_LANGUAGE', 'en');
    }

    if (!defined('CORE_COOKIE_TIMEOUT')) {
        define('CORE_COOKIE_TIMEOUT', 3600);
    }

    if (isset($_COOKIE['phpws_default_language'])){
        $language = $_COOKIE['phpws_default_language'];

        $locale = setLanguage($language);

        if ($locale == FALSE) {
            $locale = setLanguage(DEFAULT_LANGUAGE);
        }
    } else {
        $userLang = getBrowserLanguage();
        $locale_found = FALSE;

        if ($userLang[0] != DEFAULT_LANGUAGE){
            foreach ($userLang as $language) {
                $test[1] = $language;
                $test[2] = substr($language, 0, 2);
                $test[3] = $test[2] . '_' . strtoupper($test[2]);
                if (strpos($language, '-')) {
                    $test4 =  explode('-', $language);
                    $test[4] = $test4[0] . '_' . strtoupper($test4[1]);
                }

                foreach ($test as $langTest){
                    if (setLanguage($langTest)) {
                        $locale_found = TRUE;
                        $locale = $langTest;
                        setcookie('phpws_default_language', $locale, CORE_COOKIE_TIMEOUT);
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
        $GLOBALS['browser_info'] = NULL;
        return;

    }

    $agent = $_SERVER['HTTP_USER_AGENT'];
    $agentVars = explode(' ', $agent);

    foreach ($agentVars as $agent){
        $newVars[] = preg_replace('/[^\w\.\/]/', '', $agent);
    }

    list($engine, $engine_version) = explode('/', $newVars[0]);
    $browser['engine'] = $engine;
    $browser['engine_version'] = $engine_version;

    switch ($engine){
    case 'Opera':
        $platform = $newVars[1];
        $program = explode('/', $newVars[0]);

        if ($platform == 'Windows'){
            if ($newVars[2] == 'NT' && $newVars[3] == '5.0')
                $platform = 'Windows 2000';
            else
                $platform .= ' ' . $newVars[2] . ' ' . $newVars[3];
        }
        $browser['locale'] = $newVars[5];
        break;

    case 'Mozilla':
        switch ($engine_version){
        case '4.0':
            $program[0] = $newVars[2];
            $program[1] = $newVars[3];
            $platform = $newVars[4];
            if ($platform == 'Windows'){
                if ($newVars[5] == 'NT' && $newVars[6] == '5.0')
                    $platform = 'Windows 2000';
                else
                    $platform .= ' ' . $newVars[5] . ' ' . $newVars[6];
            }
            break;

        case '4.74':
            $platform = $newVars[2];
            if ($platform == 'Windows'){
                if ($newVars[3] == 'NT' && $newVars[4] == '5.0')
                    $platform = 'Windows 2000';
                else
                    $platform .= ' ' . $newVars[3] . ' ' . $newVars[4];

                $program = explode('/', $newVars[9]);
            }
            $program[0] = 'Netscape';
            $program[1] = '4.74';
            break;

        case '5.0':
            if (!isset($newVars[5])) {
                break;
            }
            if ($newVars[5] == 'Opera'){
                $platformCheck = 1;
                $program[0] = 'Opera';
                $program[1] = $newVars[6];
            } else {
                $browser['locale'] = $newVars[5];
                $platformCheck = 3;
            }
            
            $platform = $newVars[$platformCheck];

            if ($platform == 'Windows'){
                if ($newVars[$platformCheck + 1] == 'NT' && $newVars[$platformCheck + 2] == '5.0')
                    $platform = 'Windows 2000';
                else
                    $platform .= ' ' . $newVars[$platformCheck + 1] . ' ' . $newVars[$platformCheck + 2];

                if (isset($newVars[9]))
                    $program = explode('/', $newVars[9]);
                elseif(isset($newVars[8]))
                    $program = explode('/', $newVars[8]);
                else
                    $program = _('Unknown');
            } else {
                if (isset($newVars[8])){
                    if ($newVars[8] == 'Red') {
                        $program[0] = 'Red Hat';
                        $program[1] = str_replace('Hat/', '', $newVars[9]);
                    } elseif (strpos($newVars[8], '/')) {
                        $program = explode('/', $newVars[8]);
                    } else {
                        $program[0] = $program[1] = 'Unknown';
                    }
                }
                elseif (isset($newVars[7]))
                    $program = explode('/', $newVars[7]);
                else
                    $program = _('Unknown');
            }

            break;
        }
        break;

    default:
        $program[0] = $program[1] = $platform = $browser['engine_version'] = $browser['engine'] = _('Unknown');
    }// End engine switch

    if (isset($platform)) {
        $browser['platform'] = $platform;
    }

    if (isset($program)) {
        $browser['browser'] = $program[0];
        $browser['browser_version'] = $program[1];
        $GLOBALS['browser_info'] = &$browser;
    }
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
    $rootDir = PHPWS_HOME_DIR . 'config/core/i18n/';

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

function translate($module=NULL)
{
    if (empty($module)) {
        if (isset($GLOBALS['last_gettext'])) {
            $directory = &$GLOBALS['last_gettext'];
            if (is_dir($directory)) {
                bindtextdomain('messages', $directory);
                textdomain('messages');
            }
        } else {
            translate('core');
        }
    } else {
        if (!defined('PHPWS_TRANSLATION') || !PHPWS_TRANSLATION) {
            return NULL;
        }
    
        if ($module == 'core') {
            $directory = PHPWS_SOURCE_DIR . 'locale';
        } else {
            $directory = PHPWS_SOURCE_DIR . "mod/$module/locale";
        }

        if (!is_dir($directory)) {
            return NULL;
        }

        bindtextdomain('messages', $directory);
        textdomain('messages');
        $GLOBALS['last_gettext'] = $directory;
    }
}

/**
 * adds a locale prefix to a title name
 */
function translateFile($filename)
{
    return strtolower(CURRENT_LANGUAGE . '_' . $filename);
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
function &getBrowserInfo($parameter=NULL)
{
    if (!isset($GLOBALS['browser_info'])) {
        return NULL;
    } else {
        if (empty($parameter)) {
            return $GLOBALS['browser_info'];
        } else {
            return $GLOBALS['browser_info'][$parameter];
        }
    }
}

?>