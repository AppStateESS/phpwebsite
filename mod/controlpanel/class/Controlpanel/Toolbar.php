<?php

namespace controlpanel\Controlpanel;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Toolbar {

    /**
     * User options to create something new
     * @var array
     */
    private $create_options;

    /**
     * User options for the currently displayed page.
     * @var array
     */
    private $page_options;

    /**
     * User options for the entire site.
     * @var array
     */
    private $site_options;

    /**
     * User preference for themselves
     * @var array
     */
    private $user_options;

    public function addCreateOption($module_name, $content)
    {
        $module_name = \ModuleController::singleton()->getModule($module_name)->getProperName()->get();
        $this->create_options[$module_name][] = $content;
    }

    public function addPageOption($module_name, $content)
    {
        $module_name = \ModuleController::singleton()->getModule($module_name)->getProperName()->get();
        $this->page_options[$module_name][] = $content;
    }

    public function addSiteOption($module_name, $content)
    {
        $module_name = \ModuleController::singleton()->getModule($module_name)->getProperName()->get();
        $this->site_options[$module_name][] = $content;
    }

    public function addUserOptions($module_name, $content)
    {
        $module_name = \ModuleController::singleton()->getModule($module_name)->getProperName()->get();
        $this->user_options[$module_name][] = $content;
    }

    public function getUserOptions()
    {
        if (empty($this->user_options)) {
            return null;
        }

        $tpl = new \Template(array('links'=>$this->user_options));
        $tpl->setModuleTemplate('controlpanel', 'options.html');
        return $tpl->get();
    }

    public function getPageOptions()
    {
        if (empty($this->page_options)) {
            return null;
        }

        $tpl = new \Template(array('links'=>$this->page_options));
        $tpl->setModuleTemplate('controlpanel', 'options.html');
        return $tpl->get();
    }

    public function getCreateOptions()
    {
        if (empty($this->create_options)) {
            return null;
        }

        $tpl = new \Template(array('links'=>$this->create_options));
        $tpl->setModuleTemplate('controlpanel', 'options.html');
        return $tpl->get();
    }

    public function getSiteOptions()
    {
        if (empty($this->site_options)) {
            return null;
        }
        $tpl = new \Template(array('links'=>$this->site_options));
        $tpl->setModuleTemplate('controlpanel', 'options.html');
        return $tpl->get();
    }

}

?>
