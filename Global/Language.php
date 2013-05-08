<?php

/**
 * Translates text passed to it by the translate method. Depends on
 * gettext to function.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Language {

    /**
     * Domain of translation. Usually the identifier is the module name
     * @var string
     */
    private $domain = null;

    /**
     * Locale of translation (i.e. English/US, French, etc.)
     * @var string
     */
    private static $locale = null;

    /**
     * Translates a string into the current domain language. Parameters after
     * the first are used in a sprintf.
     * @return string
     */
    public function translate()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            trigger_error(t('Language->translate() expects at least one parameter'),
                    E_USER_ERROR);
        }

        if (is_array($args[0])) {
            $args = $args[0];
        }
        $args[0] = dgettext($this->domain, $args[0]);
        if (count($args) > 1) {
            return call_user_func_array('sprintf', $args);
        } else {
            return $args[0];
        }
    }

    /**
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        static $set_domains = array();
        $this->domain = $domain;
        if (isset($set_domains[$domain])) {
            return;
        }
        if ($domain == 'core') {
            $locale_directory = PHPWS_SOURCE_DIR . 'locale';
        } else {
            $locale_directory = PHPWS_SOURCE_DIR . "mod/$domain/locale";
        }
        $set_domains[$domain] = 1;
        bindtextdomain($domain, $locale_directory);
        bind_textdomain_codeset($domain, 'UTF8');
    }

    public static function setLocale($locale)
    {
        $versions = array();
        $versions[] = $locale . '.UTF-8';
        $versions[] = $locale . '.UTF8';
        $versions[] = $locale;
        self::$locale = $locale;
        return setlocale(LC_ALL, $versions);
    }

}

?>
