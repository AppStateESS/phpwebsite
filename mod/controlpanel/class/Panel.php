<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
class PHPWS_Panel {

    private $tabs;
    private $module;
    private $current_tab;

    public function __construct()
    {
        $this->current_tab = Request::singleton()->isGetVar('tab') ? Request::singleton()->getGet('tab') : null;
    }

    public function quickSetTabs(array $tabs)
    {
        $this->tabs = $tabs;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getCurrentTab()
    {
        return $this->current_tab;
    }

    public function display()
    {
        foreach ($this->tabs as $label=>$tab) {
            $link = $title = null;
            extract($tab);
            $link .= "&amp;tab=$label";
            $content = "<a href=\"$link\">$title</a>";
            Controlpanel::getToolbar()->addPageOption($this->module, $content);
        }
    }

}

?>