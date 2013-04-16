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

    public static function get($module_name, $variable_name)
    {
        $settings = self::singleton();
        if (!isset($settings->variables[$module_name][$variable_name])) {
            $settings->loadDefaultSettings($module_name, $variable_name);
        }
        return $settings->variables[$module_name][$variable_name];
    }

    public function loadDefaultSettings($module_name, $variable_name)
    {
        $manager = \ModuleManager::singleton();
        $module = $manager->getModule($module_name);
        if ($module instanceof \SettingDefaults) {
            $settings = $module->getSettingDefaults();
            if (!isset($settings[$variable_name])) {
                throw new \Exception(t('Unknown setting "%s:%s"', $module_name, $variable_name));
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
        $s = $db->addTable('Settings');
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

    final public static function singleton($reload=false)
    {
        static $settings = null;
        if ($reload || empty($settings)) {
            $settings = new Settings;
            $db = Database::newDB();
            $db->addTable('Settings');
            $db->loadSelectStatement();
            $rows = $db->fetchAll();
            foreach ($rows as $v) {
                extract($v);
                $settings->variables[$module_name][$variable_name] = $setting;
            }
        }
        return $settings;
    }

}

?>