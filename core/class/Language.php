<?php
namespace Core;
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

class Language {
    public function __construct()
    {
        /* Initialize language settings */
        if (DISABLE_TRANSLATION || !function_exists('bindtextdomain')) {
            define('CURRENT_LANGUAGE', 'en_US');
            define('PHPWS_TRANSLATION', FALSE);

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

        } else {
            define('PHPWS_TRANSLATION', TRUE);
            $this->initLanguage();
            $core_locale = PHPWS_SOURCE_DIR . 'locale';

            bindtextdomain('core', $core_locale);
            textdomain('core');

            $handle = opendir(PHPWS_SOURCE_DIR . 'mod/');

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

    public function setLanguage($language)
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
    public function initLanguage()
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
            $locale = $this->setLanguage($language);

            if ($locale == FALSE) {
                $locale = $this->setLanguage(DEFAULT_LANGUAGE);
            }
            $locale = preg_replace('/\.utf8|\.utf-8/i', '', $locale);
        } else {
            $locale_found = FALSE;

            if (!FORCE_DEFAULT_LANGUAGE && !IGNORE_BROWSER_LANGUAGE) {
                $userLang = $this->getBrowserLanguage();
                foreach ($userLang as $language) {
                    if (strpos($language, '-')) {
                        $testslash =  explode('-', $language);
                        $test[0] = $testslash[0] . '_' . strtoupper($testslash[1]);
                    }

                    $test[1] = $language;
                    $test[2] = substr($language, 0, 2);
                    $test[3] = $test[2] . '_' . strtoupper($test[2]);

                    foreach ($test as $langTest){
                        if ($this->setLanguage($langTest)) {
                            $locale_found = TRUE;
                            $locale = $langTest;
                            setcookie('phpws_default_language', $locale, time() + CORE_COOKIE_TIMEOUT);
                            break;
                        }
                    }

                    if ($locale_found) {
                        break;
                    }
                }
            }

            if ($locale_found == FALSE) {
                $locale = $this->setLanguage(DEFAULT_LANGUAGE);
                setcookie('phpws_default_language', $locale, time() + CORE_COOKIE_TIMEOUT);
            }
        }

        if ($locale != FALSE) {
            define('CURRENT_LANGUAGE', $locale);
        }
        else {
            define('CURRENT_LANGUAGE', DEFAULT_LANGUAGE);
        }

        $this->loadLanguageDefaults($locale);
    }



    public function getBrowserLanguage()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return explode(',', preg_replace("/(;q=\d\.*\d*)/", '', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
        }
        else {
            return array(DEFAULT_LANGUAGE);
        }
    }


    public function loadLanguageDefaults($language)
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

    public function doubleLanguage($language)
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
}

?>