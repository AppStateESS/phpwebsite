<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Base abstract class for all modules. Every Module class is expect to
 * extend this class to assure preparation and run time functionality.
 */
abstract class ModuleAbstract extends Data {

    /**
     * Array of dependencies for the module. Modules will not be loaded
     * until the dependency is fulfilled.
     * Format is key: name of module / value: version needed
     * @var array
     */
    protected $dependencies = null;

    /**
     * Indicates if module is currently active
     * @var boolean
     */
    protected $active = true;

    /**
     * Pointer to the manager display object
     * @var \Body
     */
    protected $display = null;

    /**
     * If true, this module has been installed on the station
     * or site
     * @var false
     */
    protected $installed = false;

    /**
     * Type of module or when it is run. Global modules are
     * always loaded. Site and station modules are only loaded
     * at their respective times.
     * @var unknown_type
     */

    /**
     * Name of the current module
     * @var string
     */
    protected $name;

    /**
     * Address to icon representing this Module
     * @var string
     */
    protected $icon;

    /**
     * The directory of THE RESOURCE itself.
     * @var string
     */
    protected $directory;

    /**
     * The url of THE RESOURCE itself
     * @var string
     */
    protected $url;

    /**
     * Contains the register object for this module
     * @var Register
     */
    protected $register;

    /**
     * A required function accessed on initialization of the software. After loaded
     * in ModuleManager::run, the run method is called defaultly and the extending
     * class can start its indirect processes.
     *
     * @see ModuleManager::run()
     * @return boolean
     */
    abstract public function run();

    /**
     * After completing constructed, this method is called by the ModuleManager.
     * It is required but doesn't have to necessarily do anything for
     * the module.
     */
    abstract public function init();

    /**
     * The constuction method for all modules based upon it. The parent of each
     * module is the manager itself (see Data::setParent). The display is referenced
     * from the manager so modules may call it directly.
     * Afterwards, the namespace, dependences, current module name, directory, and url
     * are loaded into the module.
     * @param ModuleManager $manager
     */
    public function __construct()
    {
        // This must be called for the module to be identifable.
        $this->loadNamespace();
        $this->loadDependencies();
        $this->loadModuleName();
        $this->loadDirectory();
    }

    /**
     * Returns the register object for this module
     * @param ModuleAbstract $module
     * @return Register
     */
    protected function getRegister(ModuleAbstract $module)
    {
        if (empty($this->register[$module->name])) {
            $cn = $this->getNamespace() . '\\Register';
            $this->register[$module->name] = new $cn($this, $module);
        }
        return $this->register[$module->name];
    }

    /**
     * Name of the current module
     * @param string $module_name
     */
    private function loadModuleName()
    {
        $this->name = $this->getNamespace();
    }

    private function loadDirectory()
    {
        $this->directory = ModuleManager::singleton()->getDirectory() . 'Module/' . $this->name . '/';
    }

    private function loadUrl()
    {
        $this->url = ModuleManager::singleton()->getUrl() . 'Module/' . $this->name . '/';
    }

    public function getUrl()
    {
        if (empty($this->url)) {
            $this->loadUrl();
        }
        return $this->url;
    }

    protected function loadIcon($icon_directory, $full_path = false)
    {
        if ($full_path) {
            $src = & $icon_directory;
        } else {
            $src = $this->getDirectory() . $icon_directory;
        }
        $this->icon = new \Image($src);
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Assures dependencies required by the current module have been loaded.     */
    private function loadDependencies()
    {
        if (empty($this->dependencies)) {
            return;
        }

        foreach ($this->dependencies as $module_name => $version) {
            $this->parent->loadModule($module_name);
            if ($this->parent->checkDependency($module_name, $version)) {
                # @todo note deactivation
                $this->parent->deactivateModule($module_name);
            }
        }
    }

    /**
     * Changes the module's active status to FALSE
     */
    public function deactivate()
    {
        $this->active = false;
    }

    /**
     * An expected method in the
     * @return mixed
     */
    public function display()
    {
        return false;
    }

}

?>