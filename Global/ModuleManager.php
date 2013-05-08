<?php

/**
 * Controls the loading of modules inside Beanie.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
require_once 'Global/Implementations.php';

final class ModuleManager extends Data {

    /**
     * Array of modules on the site
     * @var array
     */
    private $modules = null;
    //@todo delete if not needed
    //private $display;

    /**
     * The current requested module object
     * @var object
     */
    private $current_module = null;

    /**
     * There is a chance we don't want the manager to run the modules. This
     * is an all or nothing chance. All the modules are loaded but not run.
     * @var boolean
     */
    private $run_module_stack = true;

    /**
     * Contains directory path of current installation
     * @var string
     */
    private $directory;

    /**
     * Contains the url of the current installation
     * @var string
     */
    private $url;

    /**
     * Constructs the object, loads the Display object into it.
     * @see ModuleManager::singleton()
     */
    private function __construct()
    {
        $this->loadDirectory();
    }

    /**
     * Constructs the Module Manager object if not yet created, otherwise
     * returns the current Module Manager object.
     * @staticvar string $manager
     * @return ModuleManager
     */
    final public static function singleton()
    {
        static $manager = null;
        if (empty($manager)) {
            $manager = new ModuleManager;
        }
        return $manager;
    }

    private function loadDirectory()
    {
        $this->directory = realpath(null) . '/';
    }

    private function loadUrl()
    {
        $this->url = \Server::getHomeUrl();
    }

    public function getUrl()
    {
        if (empty($this->url)) {
            $this->loadUrl();
        }
        return $this->url;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Changes the run_module_stack to false, preventing the running
     * of the loaded modules.
     */
    public function preventRun()
    {
        $this->run_module_stack = false;
    }

    /**
     * This method is the first method called after the construction of the
     * manager singleton. It loads the module list, runs each, loads the currently
     * requested module and then calls it.
     */
    public function run()
    {
        // Loads the list of currently installed modules into memory.
        $this->loadModuleList();
        // if stack is run, each loaded module object has its run method called
        if ($this->run_module_stack) {
            $this->runModuleList();
        }

        /**
         * The module currently called via Request is loaded (whether the
         * stack above is or isn't).
         */
        $this->loadCurrentModule();

        $this->callCurrentModule();
    }

    /**
     * Looks in the Modules table of the database and pulls their titles. The
     * titles are run through the loadModule function to initialize them.
     * @see self::loadModule
     * @throws \Exception If no modules are found in the database.
     */
    private function loadModuleList()
    {
        $db = \Database::newDB();
        $t = $db->addTable('Modules');
        $t->addField('title');
        $db->loadSelectStatement();
        while ($module = $db->fetchColumn()) {
            $this->loadModule($module);
        }
        if (empty($this->modules)) {
            throw new \Exception(t('No modules found in the database for site "%s"', SITE_NAME));
        }
    }

    /**
     * Call the run method on each module.
     */
    private function runModuleList()
    {
        foreach ($this->modules as $module) {
            $module->run();
        }
    }

    /**
     *
     * @staticvar null $module_overload
     * @param string $module_name
     * @return ModuleAbstract
     * @throws \Exception
     */
    public function getModule($module_name)
    {
        static $module_overload = null;
        if (!isset($this->modules[$module_name])) {
            if (!isset($module_overload[$module_name])) {
                $module_overload[$module_name] = 1;
                $this->loadModule($module_name);
            } else {
                throw new \Exception(t('Endless Module constructor loop'));
            }
        }
        return $this->modules[$module_name];
    }

    public function getModuleDirectory($module_name)
    {
        return $this->modules[$module_name]->getDirectory();
    }

    /**
     *
     * @param string $module_name
     * @return ModuleAbstract
     */
    public static function pullModule($module_name)
    {
        $manager = self::singleton();
        return $manager->getModule($module_name);
    }

    /**
     * Takes the currently set module and run the "call" method from it.
     * The result will be a response object.
     */
    private function callCurrentModule()
    {
        // Current module is not set (e.g. home page)
        // @see self::setCurrentModule
        if (empty($this->current_module)) {
            return true;
        }
        $request = \Request::singleton();
        $state = $request->getState();

        if (!method_exists($this->current_module, $state)) {
            throw new \Exception(t('Module "%s" is missing a "%s" state.', $this->current_module->getName(), $state));
        }

        try {
            $response = $this->current_module->$state();
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                $error_response = Response::singleton();
                $error_response->setStatus('error');
                $error_response->setError(t('Current module threw an exception'));
                $error_response->printStatus();
                return false;
            } else {
                if (DISPLAY_ERRORS) {
                    throw $e;
                } else {
                    \Error::log($e);
                    \Error::errorPage();
                }
            }
        }

        if (!($response instanceof Response)) {
            throw new \Exception(t('Response object not received from called module'));
        }

        # regardless of result (failure, success, error) we print the result
        # and finish
        if ($request->isAjax()) {
            echo $response->printStatus();
            return false;
        }

        # If here, no ajax was called, user is actually viewing result page
        if ($response->isSuccess()) {
            // Form posted successfully, forward to url set in the response
            // If that isn't set, then attempt a get call on the CURRENT url
            if ($response->goForward()) {
                \Server::forward($request->getUrl());
            }
        } elseif ($response->isFailure()) {
            // Form failed, so we call "get" on the current url and repeat
            // the module call
            // reset the command stack
            $request->loadUrl();

            $response->showMessage();
            // setting request back to prevent path down post
            $request->setState(\Request::GET);
            $response = $this->current_module->get();
        } else {
            // Apparently there is an error (the only option left). We dump
            // to an error page
            // @todo determine if error page on bad post is the option we want
            \Error::errorPage();
        }

        return true;
    }

    /**
     * Loads a module into the manager object's module variable. Will not reload
     * a module unless force_reload is true
     *
     * @param string $module_name Name of module to load
     * @param boolean $force_reload If true, overwrite the array row regardless of condition
     */
    public function loadModule($module_name, $force_reload = false)
    {
        if (empty($module_name)) {
            throw new \Exception(t('ModuleManager::loadModule does not accept a blank module name'));
        }

        $module_path = "Module/$module_name/Module.php";
        if (!is_file($module_path)) {
            throw new \Exception(t('Module "%s" not found', $module_name));
        }
        require_once $module_path;

        if (isset($this->modules[$module_name]) && !$force_reload) {
            return true;
        }
        $directory = $module_name . '/locale/';
        bindtextdomain($module_name, $directory);

        $r_name = $module_name . '\Module';
        // checks the namespace class name. Autoload handles the file location
        if (!class_exists($r_name)) {
            throw new \Exception(t('Missing module "%s"', $r_name));
        }
        $module = new $r_name($this);
        $this->modules[$module_name] = $module;
        $module->init();
    }

    /**
     * Indicates if user is on the home page.
     * @return boolean
     */
    public function atHome()
    {
        return $this->at_home;
    }

    /**
     * Grabs the name of the current module, then makes a reference
     * to it in the current_module variable.
     */
    public function loadCurrentModule()
    {
        $request = Request::singleton();

        if (empty($this->modules)) {
            throw new \Exception(t('All modules must be loaded prior to current module designation'));
        }
        $module_name = $request->getModule();
        if ($module_name) {
            $this->setCurrentModule($module_name);
        }
    }

    /**
     * Sets the current module as a reference to its location in the module
     * queue.
     * @param string $module_name
     */
    public function setCurrentModule($module_name)
    {
        if (!isset($this->modules[$module_name])) {
            try {
                $this->loadModule($module_name);
            } catch (Error $e) {
                $e->errorPage();
                return;
            }
        }
        $this->current_module = $this->modules[$module_name];
    }

    /**
     * Checks this object's version dependency against the currently loaded modules.
     * Returns true if the requested module is equal to or greater than the
     * required version. False otherwise.
     *
     * @param string $module_name
     * @param string $version
     * @return boolean
     */
    public function checkDependency($module_name, $version)
    {
        return (isset($this->modules[$module_name]) && version_compare($this->modules[$module_name]->getVersion(), $version, '>='));
    }

    /**
     * Deactivates the passed module name to prevent its operation.
     * @param string $module
     */
    public function deactivateModule($module)
    {
        $this->modules[$module]->deactivate();
    }

    /**
     * Overload function for ModuleManager variables
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'sitename':
                return $this->getCurrentSite();
                break;
        }
    }

    /**
     * @todo Not sure of the purpose. Perhaps each command is recorded? Come back
     * later.
     * @return string
     */
    public static function getCommandsAttempted()
    {
        if (!isset($GLOBALS['commands_attempted'])) {
            return t('No commands attempted');
        }

        return implode('<br />', $GLOBALS['commands_attempted']);
    }

    /**
     * @todo What is the thinking on this?
     * @return string
     */
    public static function getCommandsCompleted()
    {
        if (!isset($GLOBALS['commands_completed'])) {
            return t('No commands completed');
        }
        return implode('<br />', $GLOBALS['commands_completed']);
    }

    /**
     * Registers a module to another.
     * @param ModuleAbstract $applied The Module object registered to another Module
     * @param string $host Name of the Module to register to.
     */
    public function register(ModuleAbstract $applied, $host)
    {
        $host_module = $this->getModule($host);
        $host_module->register($applied);
    }

}

?>