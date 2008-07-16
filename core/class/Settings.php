<?php

/**
 * Creates a session to hold module settings.
 * Prevents modules from having to load their config tables
 * per page.
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class PHPWS_Settings {

    /**
     * Returns the value of a setting or false if not set
     */
    function get($module, $setting=null)
    {
        if (empty($setting) && PHPWS_Settings::is_set($module)) {
            return $GLOBALS['PHPWS_Settings'][$module];
        } elseif (PHPWS_Settings::is_set($module, $setting)) {
            return $GLOBALS['PHPWS_Settings'][$module][$setting];
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
    function is_set($module, $setting=null)
    {
        if (!isset($GLOBALS['PHPWS_Settings'][$module])) {
            $result = PHPWS_Settings::load($module);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return false;
            }
        }

        if (is_array($GLOBALS['PHPWS_Settings'][$module])) {
            if (empty($setting)) {
                return true;
            } elseif (isset($GLOBALS['PHPWS_Settings'][$module][$setting])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function in_array($module, $setting, $value)
    {
        if (!PHPWS_Settings::is_set($module, $setting)) {
            return false;
        }
        return in_array($value, $GLOBALS['PHPWS_Settings'][$module][$setting]);
    }

    /**
     * Sets the module setting value
     */
    function set($module, $setting, $value=null)
    {
        if (empty($setting)) {
            return;
        }

        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                PHPWS_Settings::set($module, $key, $subval);
            }
            return true;
        }

        $GLOBALS['PHPWS_Settings'][$module][$setting] = $value;
        return true;
    }

    /*
     * Not in use and probably not usable. Removing notes on it but keeping it just 
     * in case someone is using it.
     */
    function append($module, $setting, $value)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                $result = PHPWS_Settings::append($module, $key, $subval);
                if (!$result) {
                    return false;
                }
            }
            return true;
        } elseif ( isset($GLOBALS['PHPWS_Settings'][$module][$setting]) &&
                   !is_array($GLOBALS['PHPWS_Settings'][$module][$setting])) {
            return false;
        }

        $GLOBALS['PHPWS_Settings'][$module][$setting][] = $value;
        return true;
    }


    /**
     * updates the settings table
     */
    function save($module)
    {
        if (!PHPWS_Settings::is_set($module)) {
            return false;
        }

        $db = new PHPWS_DB('mod_settings');

        $db->addWhere('module', $module);
        $db->addWhere('setting_name', array_keys($GLOBALS['PHPWS_Settings'][$module]));
        $db->delete();
        $db->reset();

        foreach ($GLOBALS['PHPWS_Settings'][$module] as $key => $value) {
            if (empty($key)) {
                continue;
            }

            $type = PHPWS_Settings::getType($value);
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
            if (PEAR::isError($result)) {
                unset($GLOBALS['PHPWS_Settings'][$module]);
                PHPWS_Settings::load($module);
                return $result;
            }
            $db->reset();
        }
        unset($GLOBALS['PHPWS_Settings'][$module]);
        PHPWS_Settings::load($module);
    }

    function loadConfig($module)
    {
        $filename = sprintf('%smod/%s/inc/settings.php', PHPWS_SOURCE_DIR, $module);

        if (is_file($filename)) {
            return $filename;
        } else {
            return null;
        }
    }

    function reset($module, $value)
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
    function load($module)
    {
        $default = PHPWS_Settings::loadConfig($module);
        if (!$default) {
            $GLOBALS['PHPWS_Settings'][$module] = 1;
            return PHPWS_Error::get(SETTINGS_MISSING_FILE, 'core', 'PHPWS_Settings::load', $module);
        }

        include $default;
        PHPWS_Settings::set($module, $settings);

        $db = new PHPWS_DB('mod_settings');
        $db->addWhere('module', $module);
        $result = $db->select();

        if (PEAR::isError($result)) {
            return $result;
        } elseif (empty($result)) {
            PHPWS_Settings::save($module);
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

                PHPWS_Settings::set($module, $value['setting_name'], $setval);
            }
        }
        return true;
    }

    function getType($value)
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
                return PHPWS_Settings::getType((int)$value);
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
    function unregister($module)
    {
        $db = new PHPWS_DB('mod_settings');
        $db->addWhere('module', $module);
        return $db->delete();
    }

}

?>