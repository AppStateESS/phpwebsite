<?php

/**
 * Creates a session to hold module settings.
 * Prevents modules from having to load their config tables
 * per page.
 *
 * @author Matt McNaney <mcnaney at gmail dot com>
 */

class PHPWS_Settings {

    /**
     * Returns the value of a setting or FALSE if not set
     */
    function get($module, $setting=NULL)
    {
        if (empty($setting) && PHPWS_Settings::is_set($module)) {
            return $_SESSION['PHPWS_Settings'][$module];
        } elseif (PHPWS_Settings::is_set($module, $setting)) {
            return $_SESSION['PHPWS_Settings'][$module][$setting];
        } else {
            return FALSE;
        }
    }

    /**
     * Checks to see if the value isset in the session
     */
    function is_set($module, $setting=NULL)
    {
        if (!isset($_SESSION['PHPWS_Settings'][$module])) {
            $result = PHPWS_Settings::load($module);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return FALSE;
            }
        }

        if (is_array($_SESSION['PHPWS_Settings'][$module])) {
            if (empty($setting)) {
                return TRUE;
            } elseif (isset($_SESSION['PHPWS_Settings'][$module][$setting])) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function in_array($module, $setting, $value)
    {
        if (!PHPWS_Settings::is_set($module, $setting)) {
            return FALSE;
        }
        return in_array($value, $_SESSION['PHPWS_Settings'][$module][$setting]);

    }

    /**
     * Sets the module setting value
     */
    function set($module, $setting, $value=NULL)
    {
        if (empty($setting)) {
            return;
        }

        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                PHPWS_Settings::set($module, $key, $subval);
            }
            return TRUE;
        }

        $_SESSION['PHPWS_Settings'][$module][$setting] = $value;
        return TRUE;
    }

    function append($module, $setting, $value)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $subval) {
                $result = PHPWS_Settings::append($module, $key, $subval);
                if (!$result) {
                    return FALSE;
                }
            }
            return TRUE;
        } elseif ( isset($_SESSION['PHPWS_Settings'][$module][$setting]) &&
                   !is_array($_SESSION['PHPWS_Settings'][$module][$setting])) {
            return FALSE;
        }

        $_SESSION['PHPWS_Settings'][$module][$setting][] = $value;
        return TRUE;
    }

    /**
     * updates the settings table
     */
    function save($module)
    {
        if (!PHPWS_Settings::is_set($module)) {
            return FALSE;
        }

        $db = & new PHPWS_DB('mod_settings');
        $db->addWhere('module', $module);
        $db->delete();
        $db->reset();

        foreach ($_SESSION['PHPWS_Settings'][$module] as $key => $value) {
            if (empty($key)) {
                continue;
            }
            $type = PHPWS_Settings::getType($value);
            $db->addValue('module', $module);
            $db->addValue('setting_name', $key);
            $db->addValue('setting_type', $type);

            switch( $type ) {
            case 1:
                $db->addValue('small_num', $value);
                break;
            case 2:
                $db->addValue('large_num', $value);
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
                return $result;
            }
            $db->reset();
        }
    }

    function loadConfig($module)
    {
        $result = PHPWS_Core::getConfigFile($module, 'settings.php');
        return $result;
    }

    /**
     * Loads the settings into the session
     */
    function load($module)
    {
        $default = PHPWS_Settings::loadConfig($module);
        if (!$default) {
            $_SESSION['PHPWS_Settings'][$module] = 1;
            return PHPWS_Error::get(SETTINGS_MISSING_FILE, 'core', 'PHPWS_Settings::load', $module);
        }
        
        include $default;
        PHPWS_Settings::set($module, $settings);

        $db = & new PHPWS_DB('mod_settings');
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
        return TRUE;
    }

    function getType($value)
    {
        switch ($value) {
        case is_numeric($value):
            if ((int)$value < 100000) {
                return 1;
            } else {
                return 2;
            }
            break;

        case is_string($value):
            if (strlen($value) < 100) {
                return 3;
            } else {
                return 4;
            }
            break;

        case is_array($value):
            return 4;
            break;

        default:
            return 4;
        }
    }

}

?>