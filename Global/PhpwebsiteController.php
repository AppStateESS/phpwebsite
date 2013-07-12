<?php

/*
 * Main controller class for phpWebSite.  Implements Controller, so it can be
 * used like any other Controller in the system.
 *
 * @author Jeremy Booker
 * @package
 */

class PhpwebsiteController implements Controller {

    private $module_array_all;
    private $module_array_active;
    private $module_stack;
    private $request;
    // This is a temporary thing to prevent Layout from running in the event of
    // a JSON request or otherwise non-HTML response.
    private $skipLayout = false;

    /**
     * Current requested module
     * @var Module
     */
    private $current_module;

    public function execute(\Request $request)
    {
        try {
            /**
             * Call each module's init method
             */
            $this->loadModuleInits();

            Session::start();
            /**
             * Moved from Bootstrap, eventually to be deprecated
             */
            if (!PHPWS_Core::checkBranch()) {
                throw new Exception('Unknown branch called');
            }

            $this->runTime($request);

            $module = $this->determineCurrentModule($request);
            if ($module) {
                $this->beforeRun($request, $this);
                $response = $module->execute($request->getNextRequest());
                $this->renderResponse($request, $response);
                $this->afterRun($request, $response);
            }
        } catch (Http\Exception $e) {
            $this->renderResponse($request, $e->getResponse());
        } catch (Exception $e) {
            $this->renderResponse($request,
                    new Http\InternalServerErrorResponse(null, $e));
        }

        $this->destructModules();

        // TODO: a more formal and less nasty way to do this, see issue #96
        PHPWS_Core::pushUrlHistory();
    }

    protected function determineCurrentModule(\Request $request)
    {
        // Try the Old Fashioned Way first
        if ($request->isVar('module')) {
            $title = $request->getVar('module');
        }

        // Try the Somewhat Old Fashioned Access Way Next
        // Accessing $_REQUEST directly because this is how access module works
        // @todo: replace this with a new shortcutting system that does not
        // modify $_REQUEST
        if (array_key_exists('module', $_REQUEST)) {
            $title = $_REQUEST['module'];
        }

        // Otherwise, get the first token off of the Request
        else {
            $title = $request->getCurrentToken();

            if ($title == '/') {
                // @todo Configured Default Module
                return null;
            }
        }

        $mr = ModuleRepository::getInstance();

        if (!$mr->hasModule($title)) {
            throw new \Http\NotFoundException($request);
        }

        $module = $mr->getModule($title);

        $mr->setCurrentModule($module);

        return $module;
    }

    private function renderResponse(\Request $request, \Response $response)
    {
        // Temporary until proper error pages are fully implemented
        // @todo customizable, editable error pages that don't dump a bunch of
        // stack if it's not needed or if debug is disabled
        if ($response instanceof \Html\NotFoundResponse) {
            Error::errorPage(404);
        }
        $view = $response->getView();

        // For Compatibility only - modules that make an end-run around the new
        // system and speak to Layout directly should return a Response
        // containing a NullView in order to skip the new rendering process.
        if ($view instanceof NullView)
            return;

        $rendered = $view->render();

        // @todo an interface to get at request headers in the Request object...
        // lol oops
        $ajax = (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) &&
                $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

        if ($view->getContentType() == 'text/html' && !$ajax) {
            Layout::add($rendered);
            $this->skipLayout = false;
        } else {
            echo $rendered;
            $this->skipLayout = true;
        }

        // TODO: Response headers
    }

    private function destructModules()
    {
        foreach (ModuleRepository::getInstance()->getActiveModules() as $mod) {
            // This is a temporary thing to prevent Layout from running in the
            // event of a JSON request or otherwise non-HTML Response.
            if ($this->skipLayout && strtolower($mod->getTitle()) == 'layout')
                continue;

            $mod->destruct();
        }
    }

    private function beforeRun(\Request $request, \Controller $controller)
    {
        foreach (ModuleRepository::getInstance()->getActiveModules() as $mod) {
            $mod->beforeRun($request, $controller);
        }
    }

    private function runTime(\Request $request)
    {
        foreach (ModuleRepository::getInstance()->getActiveModules() as $mod) {
            $mod->runTime($request);
        }
    }

    private function afterRun(\Request $request, \Response &$response)
    {
        foreach (ModuleRepository::getInstance()->getActiveModules() as $mod) {
            $mod->afterRun($request, $response);
        }
    }

    private function loadModuleInits()
    {
        foreach (ModuleRepository::getInstance()->getActiveModules() as $mod) {
            if ($mod->isActive()) {
                $mod->init();
            }
        }
    }

    /**
     * Returns a Module subclass based on the $module_title.
     * If the Module.php file is not found, an exception is thrown.
     * @param string $module_title
     * @return \Module
     * @throws \Exception Module.php is missing
     */
    public function getModuleByTitle($module_title)
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

    public function addModule(Module $module)
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
            throw new \Exception(t('Module "%s" not found', $module_name));
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
            $row = array_map('trim', $row);
            if (is_file(PHPWS_SOURCE_DIR . 'mod/' . $row['title'] . '/Module.php')) {
                $module = $this->loadModuleValues($row);
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

    /**
     *
     * @param string $module_title
     * @return \Module
     * @throws Exception
     */
    public function getModule($module_title)
    {
        $module_title = (string) $module_title;
        if (!isset($this->module_stack[$module_title])) {
            throw new Exception(t('Module "%s" does not exist', $module_title));
        }
        return $this->module_stack[$module_title];
    }



}

?>
