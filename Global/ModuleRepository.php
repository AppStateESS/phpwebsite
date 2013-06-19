<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

final class ModuleRepository
{
    private static $INSTANCE;

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            self::$INSTANCE = new ModuleRepository();
        }

        return self::$INSTANCE;
    }

    protected $modules;
    protected $currentModule;

    protected function __construct()
    {
        $this->modules = array();
        $this->modules[] = new GlobalModule();

        $this->loadSiteModules();
    }

    protected function loadSiteModules()
    {
        $db = Database::newDB();
        $mods = $db->addTable('modules');
        $mods->addOrderBy('priority');
        $db->loadSelectStatement();

        while($row = $db->fetch()) {
            $row = array_map('trim', $row);
            if(is_file(PHPWS_SOURCE_DIR . 'mod/' . $row['title'] . '/Module.php')) {
                $module = $this->loadModule($row);
            } else {
                $module = $this->loadCompatibilityModule($row);
            }
            $this->addModule($module);
        }
        if(count($this->getActiveModules()) == 0) {
            // @todo better exceptions
            throw new \Exception(t('No active modules installed'));
        }
    }

    protected function addModule($module)
    {
        $this->modules[] = $module;
    }

    protected function loadModule(array $values)
    {
        $title = $values['title'];
        $module_path = PHPWS_SOURCE_DIR . "mod/$title/Module.php";
        if (!is_file($module_path)) {
            throw new \Exception(t('Module "%s" not found', $title));
        }
        require_once $module_path;
        $namespace = "$title\\Module";
        $module = new $namespace;

        /**
         * These are in the old modules table, but will not be used.
         * @todo Once all modules are updated, dump these columns.
         */
        unset($values['register']);
        unset($values['unregister']);
        $module->setVars($values);
        $module->loadData();
        return $module;
    }

    protected function loadCompatibilityModule(array $values)
    {
        $module = new CompatibilityModule;
        $module->setVars($values);
        $module->loadData();
        $module->setDeprecated(1);
        return $module;
    }

    public function setCurrentModule(Module $module)
    {
        $this->currentModule = $module;
    }

    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    public function getAllModules()
    {
        return $this->modules;
    }

    public function getActiveModules()
    {
        $active = array();
        foreach($this->modules as $module) {
            if($module->isActive()) $active[] = $module;
        }
        return $active;
    }

    public function getInactiveModules()
    {
        $active = array();
        foreach($this->modules as $module) {
            if(!$module->isActive()) $active[] = $module;
        }
        return $active;
    }

    public function getModule($title)
    {
        foreach($this->getAllModules() as $module) {
            if($module->getTitle() == $title) {
                return $module;
            }
        }

        // @todo better exceptions
        throw new \Exception("No module registered with title $title");
    }

    public function hasModule($title)
    {
        foreach($this->getAllModules() as $module) {
            if($module->getTitle() == $title) {
                return true;
            }
        }
        return false;
    }
}

?>
