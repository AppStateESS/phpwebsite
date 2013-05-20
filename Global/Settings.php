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
        if (strtolower($module_name != 'global')) {
            $module = new GlobalModule;
        } else {
            $manager = \ModuleManager::singleton();
            $module = $manager->getModule($module_name);
        }
        if ($module instanceof \SettingDefaults) {
            $settings = $module->getSettingDefaults();
            if (!isset($settings[$variable_name])) {
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
        $s = $db->addTable('settings');
        $s->addWhere('module_name', $module_name);
        $s->addWhere('variable_name', $variable_name);
        try {
            $db->delete();
        } catch (\Exception $e) {
            //@todo better error handling here
            throw $e;
        }

        $s->addValue('module_name', $module_name);
        $s->addValue('variable_name', $variable_name);
        $s->addValue('setting', $setting);
        $db->insert();
    }

    final public static function singleton($reload = false)
    {
        if ($reload || empty(self::$settings)) {
            self::$settings = new Settings;
            $db = Database::newDB();
            $db->addTable('settings');
            $db->loadSelectStatement();
            $rows = $db->fetchAll();
            foreach ($rows as $v) {
                extract($v);
                self::$settings->variables[$module_name][$variable_name] = $setting;
            }
        }
        return self::$settings;
    }

}

?>