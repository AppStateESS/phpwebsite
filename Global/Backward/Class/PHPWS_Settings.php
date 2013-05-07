<?php

/**
 * Creates a session to hold module settings.
 * Prevents modules from having to load their config tables
 * per page.
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id: Settings.php 7776 2010-06-11 13:52:58Z jtickle $
 */
class PHPWS_Settings {

    /**
     * Returns the value of a setting or false if not set
     */
    public static function get($module, $setting = null)
    {
        return Settings::get($module, $setting);
    }

    /**
     * Checks to see if the value isset in the global variable.
     * is_set does not load all of the modules at once to allow
     * checking against default settings. If all were pulled at once,
     * newly added settings would get ignored.
     */
    public static function is_set($module, $setting = null)
    {
        $settings = self::singleton();
        return isset($settings->variables[$module_name][$variable_name]);
    }

    public static function in_array($module, $setting, $value)
    {
        $settings = self::singleton();
        if (!isset($settings->variables[$module][$setting])) {
            return false;
        }
        return in_array($value, $settings->variables[$module][$setting]);
    }

    /**
     * Sets the module setting value
     */
    public static function set($module, $setting, $value = null)
    {
        $settings = self::singleton();
        $settings->set($module, $setting, $value);
    }

    /**
     * updates the settings table
     */
    public static function save($module)
    {
        $module = null;
        return true;
    }

    public static function loadConfig($module)
    {
        $filename = "mod/$module/inc/settings.php";
        return is_file($filename) ? $filename : null;
    }

    public static function reset($module, $value)
    {
        $default = PHPWS_Settings::loadConfig($module);
        if (!$default) {
            return PHPWS_Error::get(SETTINGS_MISSING_FILE, 'core', 'PHPWS_Settings::reset', $module);
        }

        include $default;

        if (isset($settings[$value])) {
            PHPWS_Settings::set($module, $value, $settings[$value]);
            $result = PHPWS_Settings::save($module);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loads the settings into the session
     *
     */
    public static function load($module)
    {
        $default = PHPWS_Settings::loadConfig($module);
        if (!$default) {
            $GLOBALS['PHPWS_Settings'][$module] = 1;
            throw new Exception(t('Missing module settings.php file.'));
        }

        include $default;
        PHPWS_Settings::set($module, $settings);

        $db = \Database::newDB();
        $table = $db->addTable('mod_settings');
        $table->addWhere('module', $module);
        $result = $db->select();

        if (empty($result)) {
            PHPWS_Settings::save($module);
        } else {
            foreach ($result as $value) {
                switch ($value['setting_type']) {
                    case 1:
                        $setval = $value['small_num'];
                        break;
                    case 2:
                        $setval = $value['large_num'];
                        break;
                    case 3:
                        $setval = $value['small_char'];
                        break;
                    case 4:
                        $setval = $value['large_char'];
                        break;
                }

                PHPWS_Settings::set($module, $value['setting_name'], $setval);
            }
        }
        return true;
    }

    public static function getType($value)
    {
        switch (gettype($value)) {
            case 'NULL':
                return 3;
                break;

            case 'boolean':
            case 'integer':
                if ((int) $value < 32700) {
                    return 1;
                } else {
                    return 2;
                }
                break;

            case 'double':
            case 'string':
                if (strpos($value, '.') === false && is_numeric($value)) {
                    return PHPWS_Settings::getType((int) $value);
                }
                if (strlen($value) < 100) {
                    return 3;
                } else {
                    return 4;
                }
                break;

            case 'object':
            case 'array':
                return 4;
                break;

            default:
                return 4;
        }
    }

    /**
     * Unregisters a module's settings
     */
    public static function unregister($module)
    {
        $db = new PHPWS_DB('mod_settings');
        $db->addWhere('module', $module);
        return $db->delete();
    }

    /**
     * Clears the settings global
     */
    public static function clear()
    {
        unset($GLOBALS['PHPWS_Settings']);
    }

}

?>