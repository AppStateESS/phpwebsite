<?php

/**
 * Stores and retrieves settings within Modules.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Settings extends Data {

    public $variables;
    private static $settings;

    public static function get($module_name, $variable_name)
    {
        if (empty(self::$settings)) {
            self::singleton();
        }
        if (!isset(self::$settings->variables[$module_name][$variable_name])) {
            self::$settings->loadDefaultSettings($module_name, $variable_name);
        }
        return self::$settings->variables[$module_name][$variable_name];
    }

    public function loadDefaultSettings($module_name, $variable_name)
    {
        $module = ModuleRepository::getInstance()->getModule($module_name);
        if ($module instanceof \SettingDefaults) {
            $settings = $module->getSettingDefaults();
            if (!array_key_exists($variable_name, $settings)) {
                throw new \Exception(t('Unknown setting "%s:%s"', $module_name,
                        $variable_name));
            }
            $this->set($module_name, $variable_name, $settings[$variable_name]);
        } else {
            throw new \Exception(t('Module does not extend SettingDefaults'));
        }
    }

    public static function set($module_name, $variable_name, $setting)
    {
        $settings = self::singleton();
        $settings->variables[$module_name][$variable_name] = $setting;
        $db = \Database::newDB();
        if (!$db->tableExists('settings')) {
            self::createSettingsTable();
        }
        $s = $db->addTable('settings');
        $db->setConditional($db->createConditional($s->getFieldConditional('module_name',
                                $module_name),
                        $s->getFieldConditional('variable_name', $variable_name),
                        'and'));
        $db->delete();
        $s->reset();
        if (is_array($setting)) {
            foreach ($setting as $value) {
                $s->addValue('module_name', $module_name);
                $s->addValue('variable_name', $variable_name);
                $s->addValue('setting', $value);
                $db->insert();
                $s->resetValues();
            }
        } else {
            $s->addValue('module_name', $module_name);
            $s->addValue('variable_name', $variable_name);
            $s->addValue('setting', $setting);
            $db->insert();
        }
    }

    public static function createSettingsTable()
    {
        $db = \Database::newDB();
        if ($db->tableExists('settings')) {
            return;
        }
        $settings = $db->addTable('settings');
        $idx[] = $settings->addDataType('module_name', 'varchar')->setIsNull(false);
        $idx[] = $settings->addDataType('variable_name', 'varchar')->setIsNull(false);
        $settings->addDataType('setting', 'varchar')->setIsNull(true);
        $settings->create();
        $index = new \Database\Index($idx, 'settings_idx');
        $index->create();
    }

    private static function singleton($reload = false)
    {
        if ($reload || empty(self::$settings)) {
            self::$settings = new Settings;
            $db = Database::newDB();
            if (!$db->tableExists('settings')) {
                self::createSettingsTable();
            }
            $db->addTable('settings');
            $db->loadSelectStatement();
            $rows = $db->fetchAll();
            foreach ($rows as $v) {
                $module_name = $variable_name = $setting = null;
                extract($v);
                if (isset(self::$settings->variables[$module_name][$variable_name])) {
                    if (!is_array(self::$settings->variables[$module_name][$variable_name])) {
                        $old_val = self::$settings->variables[$module_name][$variable_name];
                        unset(self::$settings->variables[$module_name][$variable_name]);
                        self::$settings->variables[$module_name][$variable_name][] = $old_val;
                    }
                    self::$settings->variables[$module_name][$variable_name][] = $setting;
                } else {
                    self::$settings->variables[$module_name][$variable_name] = $setting;
                }
            }
        }
        return self::$settings;
    }

}

?>
