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
abstract class Module extends Data implements Controller {

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
    protected $active;
    protected $title;
    protected $priority;

    /**
     * Name of the current module
     * @var string
     */
    protected $proper_name;

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
     * If true, this module is a version created prior to 1.8.2
     * @var boolean
     */
    protected $deprecated = 0;

    /**
     * Version according to database
     * @var string
     */
    protected $version;

    /**
     * Version according to files
     * @var string
     */
    protected $file_version;

    /**
     * If you would like code from your module to run on every request, after
     * sessions are available and immediately before the target module is loaded,
     * override the beforeRun function.  By default, it does nothing.
     *
     * You may change the request at this time, or interact with the controller
     * before it runs.  The request is passed in by reference, so if you need
     * to, you can completely replace it with a different Request object.
     *
     * @see ModuleManager::beforeRun()
     * @return void
     */
    public function beforeRun(\Request $request, \Controller $controller)
    {

    }

    /**
     * If you would like code from your module to run on every request,
     * immediately after the target module is run, override the afterRun function.
     * By default, it does nothing.
     *
     * You may change the response at this time and access the request for
     * reference.  The response is passed in by reference, so if you need to,
     * you can completely replace it with a different Response object.
     *
     * @see ModuleManager::afterRun()
     * @return void
     */
    public function afterRun(\Request $request, \Response $response)
    {

    }

    /**
     * After completing constructed, this method is called by the ModuleManager.
     * If you would like code from your module to run on ever request, before
     * sessions are available, override the init function.  By default, it does
     * nothing.
     */
    public function init()
    {

    }

    public function runTime(\Request $request)
    {

    }

    /**
     * After the primary module has completed execution, this method is called
     * by the ModuleManager, right before phpWebSite terminates.  Override and
     * implement this method if you would like for your module to run code at
     * the end of any request.  By default, it does nothing.
     *
     * NOTE: Not to be confused with __destruct, this is NOT called upon
     * garbage collection, it is called at the end of phpWebSite execution.
     */
    public function destruct()
    {

    }

    /**
     * This method is how your module tells phpWebSite which controller to run
     * for a particular request.  We highly recommend using the Request object
     * to ensure that every single possible interaction with your module happens
     * using proper HTTP URLs, and we strongly discourage using query string
     * parameters to determine how the request is routed within your module.
     *
     * NOTE: If your module is very simple, you may implement Controller on your
     * Module instance and then implement an execute function.  We strongly
     * discourage this and highly recommend that you use your Module instance to
     * return a HttpRequest object, especially if you want RESTful interaction.
     *
     * @param $request Request The Request object for this request
     * @return Controller A Controller object that will be called to run your
     *                    module
     */
    public abstract function getController(Request $request);

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
        $this->title = new \Variable\Attribute(null, 'title');
        $this->proper_name = new \Variable\TextOnly(null, 'proper_name');
    }

    public function execute(\Request $request)
    {
        $controller = $this->getController($request);

        if (!($controller instanceof Controller)) {
            throw new \Exception(t('Object returned by getController was not a Controller.'));
        }

        // TODO: Implement event manager and fire a beforeExecute event

        $response = $controller->execute($request);

        // TODO: Implement event manager and fire an afterExecute event

        return $response;
    }

    public function loadData()
    {
        // This must be called for the module to be identifable.
        $this->loadNamespace();
        $this->loadDependencies();
        $this->loadDirectory();
        $this->loadUrl();
        $this->loadDomain();
    }

    public function loadDomain()
    {
        if (empty($this->directory)) {
            throw new Exception(t('Module directory must be loaded before loadDomain'));
        }
        bindtextdomain($this->title, $this->directory . 'locale/');
    }

    /**
     * Returns the register object for this module
     * @param Module $module
     * @return Register
     */
    protected function getRegister(Module $module)
    {
        if (empty($this->register[$module->name])) {
            $cn = $this->getNamespace() . '\\Register';
            $this->register[$module->name] = new $cn($this, $module);
        }
        return $this->register[$module->name];
    }

    private function loadDirectory()
    {
        $this->directory = PHPWS_SOURCE_DIR . 'mod/' . $this->title . '/';
    }

    private function loadUrl()
    {
        $this->url = PHPWS_SOURCE_HTTP . 'mod/' . $this->title . '/';
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

    public function setTitle($title)
    {
        $this->title->set($title);
    }

    public function getTitle()
    {
        return (string) $this->title;
    }

    public function setProperName($name)
    {
        $this->proper_name->set($name);
    }

    public function getProperName()
    {
        return (string) $this->proper_name;
    }

    public function setPriority($priority)
    {
        $this->priority = (int) $priority;
    }

    public function getPriority()
    {
        return (int) $this->priority;
    }

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return (string) $this->version;
    }

    public function setDeprecated($deprecated)
    {
        $this->deprecated = (bool) $deprecated;
    }

    public function isDeprecated($deprecated)
    {
        return (bool) $this->deprecated;
    }

}

?>
