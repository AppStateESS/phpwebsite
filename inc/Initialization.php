<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    define('PHPWS_SOURCE_DIR', str_replace('inc/', '', dirname(__FILE__)));
}

if (!defined('PHPWS_SOURCE_HTTP')) {
    define('PHPWS_SOURCE_HTTP', './');
}
/**
 * Prior to 1.7.0, all settings were in a local config.php file.
 * Most settings were moved to defines.php and were server-wide
 * In case the config.php file hasn't been edited, the below
 * check tries to prevent a redeclaration.
 **/
if (!defined('DB_ALLOW_TABLE_INDEX')) {
    require_once PHPWS_SOURCE_DIR . 'core/conf/defines.php';
}

require_once PHPWS_SOURCE_DIR . 'core/conf/language.php';

if (defined('DATE_SET_SERVER_TIME_ZONE')) {
    date_default_timezone_set(DATE_SET_SERVER_TIME_ZONE);
}

if (!defined('PHPWS_HOME_DIR')) {
    define('PHPWS_HOME_DIR', './');
}

if (!defined('IGNORE_BROWSER_LANGUAGE')) {
    define('IGNORE_BROWSER_LANGUAGE', false);
}

//require_once PHPWS_SOURCE_DIR . 'core/inc/autoload.php';

$language = new core\Language;

define('PHPWS_HOME_HTTP', core\Core::getHomeHttp());
loadBrowserInformation();

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

function getBrowser()
{
    if (!isset($GLOBALS['browser'])) {
        return NULL;
    } else {
        return $GLOBALS['browser'];
    }
}

function __autoload($class_name)
{
    if (strstr($class_name, '\\')) {
        $ns = explode('\\', $class_name);
        $cls = array_pop($ns);
        $base = array_pop($ns);
        $filename = $cls . '.php';
        if (!empty($base)) {
            if ($base == 'core') {
                $file = PHPWS_SOURCE_DIR . 'core/class/' . $filename;
            } else {
                $file = PHPWS_SOURCE_DIR . "mod/$base/class/$filename";
            }
        }
    } else {
        // autoload cannot operate without namespaces
        return false;
    }
    echo $file . '<br>';
    if (!is_file($file)) {
        return false;
    }
    require_once $file;
    return true;
}

?>