<?php

/*
 * Main controller class for the project
 *
 * @author Jeremy Booker
 * @package
 */

final class ModuleController {

    static private $controller;
    private $module_array_all;
    private $module_array_active;
    private $module_stack;

    /**
     * Current requested module
     * @var ModuleAbstract
     */
    private $current_module;

    protected function __construct()
    {
        $global_module = new GlobalModule;
        $this->module_stack['Global'] = $global_module;
    }

    /**
     *
     * @return ModuleController
     */
    public static function singleton()
    {
        if (empty(self::$controller)) {
            self::$controller = new ModuleController;
        }
        return self::$controller;
    }

    public function execute()
    {
        $this->loadSiteModules();

        if (strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) === FALSE) {
            $this->forwardInfo();
        }

        /**
         * Call each module's init method
         */
        $this->loadModuleInits();

        Session::start();

        /**
         * Call each module's run method
         */
        $this->loadRunTime();

        $this->loadCurrentModule();

        if ($this->current_module) {
            $this->callCurrentModule();
        }

        $this->destructModules();
    }

    private function callCurrentModule()
    {
        // Current module is not set (e.g. home page)
        // @see self::setCurrentModule
        if (empty($this->current_module)) {
            throw new \Exception(t('Current module is not set'));
        }
        $request = \Request::singleton();
        $state = $request->getState();
        if (!method_exists($this->current_module, $state)) {
            throw new \Exception(t('Module "%s" is missing a "%s" state.',
                    $this->current_module->getName(), $state));
        }
        $this->current_module->$state();
        return true;
    }

    private function destructModules()
    {
        foreach ($this->module_stack as $mod) {
            $mod->destruct();
        }
    }

    /**
     * Grabs the name of the current module, then makes a reference
     * to it in the current_module variable.
     */
    private function loadCurrentModule()
    {
        $request = Request::singleton();
        if (empty($this->module_stack)) {
            throw new \Exception(t('All modules must be loaded prior to current module designation'));
        }
        $module_name = $request->getModule();
        if ($module_name) {
            // We are catching this as a bad module name could just be a badly
            // entered url.
            try {
                $this->setCurrentModule($module_name);
            } catch (\Exception $e) {
                Error::errorPage('404');
            }
        }
    }

    private function loadRunTime()
    {
        foreach ($this->module_stack as $mod) {
            if ($mod->isActive()) {
                $mod->run();
            }
        }
    }

    private function loadModuleInits()
    {
        foreach ($this->module_stack as $mod) {
            if ($mod->isActive()) {
                $mod->init();
            }
        }
    }

    /**
     * Returns a ModuleAbstract extended Module based on the $module_title.
     * If the Module.php file is not found, an exception is thrown.
     * @param string $module_title
     * @return \ModuleAbstract
     * @throws \Exception Module.php is missing
     */
    public function loadModuleByTitle($module_title)
    {
        $module_path = PHPWS_SOURCE_DIR . "mod/$module_title/Module.php";
        if (!is_file($module_path)) {
            throw new \Exception(t('Module "%s" not found', $module_title));
        }
        require_once $module_path;
        $namespace = "$module_title\\Module";
        $module = new $namespace;
        return $module;
    }

    /**
     * Loads a Module object based on the values array. This array is a row
     * from the modules table.
     * @param array $values
     * @return \Module
     */
    private function loadPHPWSModule(array $values)
    {
        $module = new Module;
        $module->setVars($values);
        $module->loadData();
        return $module;
    }

    public function addModule(ModuleAbstract $module)
    {
        $this->module_stack[$module->getTitle()] = $module;
    }

    /**
     * Sets the current module as a reference to its location in the module
     * queue.
     * @param string $module_name
     */
    public function setCurrentModule($module_name)
    {
        if (!isset($this->module_stack[$module_name])) {
            $this->loadModuleByTitle($module_name);
        }
        if (!$this->module_stack[$module_name]->isActive()) {
            throw new \Exception('Inactive module accessed');
        }
        $this->current_module = $this->module_stack[$module_name];
    }

    public function loadSiteModules()
    {
        $db = Database::newDB();
        $mods = $db->addTable('modules');
        $mods->addOrderBy('priority');
        $db->loadSelectStatement();
        while ($row = $db->fetch()) {
            if (isset($row['deprecated']) && !$row['deprecated']) {
                $module = $this->loadModuleByTitle($row['title']);
            } else {
                $module = $this->loadPHPWSModule($row);
            }
            $this->addModule($module);
            $this->module_array_all[$row['title']] = $row;
            if ($row['active']) {
                $this->module_array_active[$row['title']] = $row;
            }
        }
        if (empty($this->module_array_active)) {
            throw new \Exception(t('No active active modules installed'));
        }
    }

    public function getModuleArrayAll()
    {
        return $this->module_array_all;
    }

    public function getModuleArrayActive()
    {
        return $this->module_array_active;
    }

    public function getModuleStack()
    {
        return $this->module_stack;
    }

    public function getCurrentModuleTitle()
    {
        if (empty($this->current_module)) {
            return null;
        } else {
            return $this->current_module->getTitle();
        }
    }

    public function moduleIsInstalled($module_title)
    {
        return isset($this->module_stack[$module_title]);
    }

    public function getModule($module_title)
    {
        if (!isset($this->module_stack[$module_title])) {
            throw new Exception(t('Module "%s" does not exist', $module_title));
        }
        return $this->module_stack[$module_title];
    }

    private function forwardInfo()
    {
        $url = PHPWS_Core::getCurrentUrl();

        if ($url == 'index.php' || $url == '') {
            return;
        }

        if (UTF8_MODE) {
            $preg = '/[^\w\-\pL]/u';
        } else {
            $preg = '/[^\w\-]/';
        }

        // Should ignore the ? and everything after it
        $qpos = strpos($url, '?');
        if ($qpos !== FALSE) {
            $url = substr($url, 0, $qpos);
        }

        $aUrl = explode('/', $url);
        $module = array_shift($aUrl);

        $mods = PHPWS_Core::getModules(true, true);

        if (!in_array($module, $mods)) {
            $GLOBALS['Forward'] = $module;
            return;
        }

        if (preg_match('/[^\w\-]/', $module)) {
            return;
        }

        $_REQUEST['module'] = $_GET['module'] = & $module;

        $count = 1;
        $continue = 1;
        $i = 0;

        // Try and save some old links references
        if (count($aUrl) == 1) {
            $_GET['id'] = $_REQUEST['id'] = $aUrl[0];
            return;
        }

        while (isset($aUrl[$i])) {
            $key = $aUrl[$i];
            if (!$i && is_numeric($key)) {
                $_GET['id'] = $key;
                return;
            }
            $i++;
            if (isset($aUrl[$i])) {
                $value = $aUrl[$i];
                if (preg_match('/&/', $value)) {
                    $remain = explode('&', $value);
                    $j = 1;
                    $value = $remain[0];
                    while (isset($remain[$j])) {
                        $sub = explode('=', $remain[$j]);
                        $_REQUEST[$sub[0]] = $_GET[$sub[0]] = $sub[1];
                        $j++;
                    }
                }

                $_GET[$key] = $_REQUEST[$key] = $value;
            }
            $i++;
        }
    }

}

?>