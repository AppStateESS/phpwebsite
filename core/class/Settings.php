<?php
namespace core;
/**
 * Creates a session to hold module settings.
 * Prevents modules from having to load their config tables
 * per page.
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Settings {

    /**
     * Returns the value of a setting or false if not set
     */
    public static function get($module, $setting=null)
    {
        if (empty($setting) && Settings::is_set($module)) {
            return $GLOBALS['Settings'][$module];
        } elseif (Settings::is_set($module, $setting)) {
            return $GLOBALS['Settings'][$module][$setting];
        } else {
            return null;
        }
    }

    /**
     * Checks to see if the value isset in the global variable.
     * is_set does not load all of the modules at once to allow
     * checking against default settings. If all were pulled at once,
     * newly added settings would get ignored.
     */
    public static function is_set($module, $setting=null)
    {
        if (!isset($GLOBALS['Settings'][$module])) {
            $result = Settings::load($module);
            if (Error::isError($result)) {
                Error::log($result);
                return false;
            }
        }

        if (is_array($GLOBALS['Settings'][$module])) {
            if (empty($setting)) {
                return true;
            } elseif (isset($GLOBALS['Settings'][$module][$setting])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function in_array($module, $setting, $value)
    {
        if (!Settings::is_set($module, $setting)) {
            return false;
        }
        return in_array($value, $GLOBALS['Settings'][$module][$setting]);
    }

    /**
     * Sets the module setting value
     */
    public static function set($module, $setting, $value=null)
    {
        if (empty($setting)) {
            return;
        }

        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                Settings::set($module, $key, $subval);
            }
            return true;
        }

        $GLOBALS['Settings'][$module][$setting] = $value;
        return true;
    }

    /*
     * Not in use and probably not usable. Removing notes on it but keeping it just
     * in case someone is using it.
     */
    public static function append($module, $setting, $value)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                $result = Settings::append($module, $key, $subval);
                if (!$result) {
                    return false;
                }
            }
            return true;
        } elseif ( isset($GLOBALS['Settings'][$module][$setting]) &&
        !is_array($GLOBALS['Settings'][$module][$setting])) {
            return false;
        }

        $GLOBALS['Settings'][$module][$setting][] = $value;
        return true;
    }


    /**
     * updates the settings table
     */
    public static function save($module)
    {
        if (!Settings::is_set($module)) {
            return false;
        }

        $db = new DB('mod_settings');

        $db->addWhere('module', $module);
        $db->addWhere('setting_name', array_keys($GLOBALS['Settings'][$module]));
        $db->delete();
        $db->reset();

        foreach ($GLOBALS['Settings'][$module] as $key => $value) {
            if (empty($key)) {
                continue;
            }

            $type = Settings::getType($value);
            $db->addValue('module', $module);
            $db->addValue('setting_name', $key);
            $db->addValue('setting_type', $type);

            switch( $type ) {
                case 1:
                    $db->addValue('small_num', (int)$value);
                    break;
                case 2:
                    $db->addValue('large_num', (int)$value);
                    break;

                case 3:
                    $db->addValue('small_char', $value);
                    break;

                case 4:
                    $db->addValue('large_char', $value);
                    break;
            }
            $result = $db->insert();
            if (Error::isError($result)) {
                unset($GLOBALS['Settings'][$module]);
                Settings::load($module);
                return $result;
            }
            $db->reset();
        }
        unset($GLOBALS['Settings'][$module]);
        Settings::load($module);
    }

    public static function loadConfig($module)
    {
        $filename = sprintf('%smod/%s/inc/settings.php', PHPWS_SOURCE_DIR, $module);

        if (is_file($filename)) {
            return $filename;
        } else {
            return null;
        }
    }

    public static function reset($module, $value)
    {
        $default = Settings::loadConfig($module);
        if (!$default) {
            return Error::get(SETTINGS_MISSING_FILE, 'core', 'Settings::reset', $module);
        }

        include $default;

        if (isset($settings[$value])) {
            Settings::set($module, $value, $settings[$value]);
            $result = Settings::save($module);
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
        $default = Settings::loadConfig($module);
        if (!$default) {
            $GLOBALS['Settings'][$module] = 1;
            return Error::get(SETTINGS_MISSING_FILE, 'core', 'Settings::load', $module);
        }

        include $default;
        Settings::set($module, $settings);

        $db = new DB('mod_settings');
        $db->addWhere('module', $module);
        $result = $db->select();

        if (Error::isError($result)) {
            return $result;
        } elseif (empty($result)) {
            Settings::save($module);
        } else {
            foreach ($result as $key => $value) {
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

                Settings::set($module, $value['setting_name'], $setval);
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
                if ((int)$value < 32700) {
                    return 1;
                } else {
                    return 2;
                }
                break;

            case 'double':
            case 'string':
                if (strpos($value, '.') === false && is_numeric($value)) {
                    return Settings::getType((int)$value);
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
        $db = new DB('mod_settings');
        $db->addWhere('module', $module);
        return $db->delete();
    }

    /**
     * Clears the settings global
     */
    public static function clear()
    {
        unset($GLOBALS['Settings']);
    }

}

class PHPWS_Settings extends Settings {}

?>