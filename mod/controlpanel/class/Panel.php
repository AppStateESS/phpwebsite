<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::requireConfig('controlpanel');
Core\Core::initModClass('controlpanel', 'Tab.php');

class PHPWS_Panel{
    public $itemname     = null;
    public $tabs         = null;
    public $content      = null;
    public $module       = null;
    public $panel        = null;
    public $_secure      = false;

    public function __construct($itemname=null)
    {
        if (isset($itemname)) {
            $this->setItemname($itemname);
        }
    }

    public function disableSecure()
    {
        $this->_secure = false;
    }

    public function enableSecure()
    {
        $this->_secure = true;
    }

    public function quickSetTabs($tabs)
    {
        $count = 1;
        foreach ($tabs as $id=>$info){
            $tab = new PHPWS_Panel_Tab;
            $tab->setId($id);

            if (!isset($info['title'])) {
                return PHPWS_Error::get(CP_MISSING_TITLE, 'controlpanel', 'quickSetTabs');
            } else {
                $tab->setTitle($info['title']);
            }

            if (!isset($info['link'])) {
                return PHPWS_Error::get(CP_MISSING_LINK, 'controlpanel', 'quickSetTabs');
            } else {
                $tab->setLink($info['link']);
            }

            if (!isset($info['itemname'])) {
                $tab->setItemname($this->itemname);
            }

            if (isset($info['strict'])) {
                $tab->isStrict();
            }

            if (isset($info['link_title'])) {
                $tab->setLinkTitle($info['link_title']);
            }

            $tab->setOrder($count);
            $count++;
            $this->tabs[$id] = $tab;
        }

        return true;
    }


    public function setTabs($tabs)
    {
        if (!is_array($tabs)) {
            return PHPWS_Error::get(CP_BAD_TABS, 'controlpanel', 'setTabs');
        }

        $this->tabs = & $tabs;
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function dropTab($id)
    {
        unset($this->tabs[$id]);
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setItemname($itemname)
    {
        $this->itemname = $itemname;
    }

    public function getItemname()
    {
        return $this->itemname;
    }


    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setPanel($panel)
    {
        $this->panel = $panel;
    }

    public function getPanel()
    {
        return $this->panel;
    }

    public function setCurrentTab($tab)
    {
        $itemname = $this->getItemname();
        $_SESSION['Panel_Current_Tab'][$itemname] = $tab;
    }

    public function getCurrentTab()
    {
        $itemname = $this->getItemname();

        if (isset($_REQUEST['tab']) &&
        isset($this->tabs[$_REQUEST['tab']]) &&
        $itemname == $this->tabs[$_REQUEST['tab']]->itemname)
        $this->setCurrentTab($_REQUEST['tab']);


        if (isset($_SESSION['Panel_Current_Tab'][$itemname]))
        return $_SESSION['Panel_Current_Tab'][$itemname];
        else {
            $currentTab = $this->getFirstTab();
            $this->setCurrentTab($currentTab);
            return $currentTab;
        }
    }

    public function getFirstTab()
    {
        Core\Core::initModClass('controlpanel', 'Tab.php');
        $result = null;

        $tabs = $this->getTabs();

        if (isset($tabs)){
            $tab = array_shift($tabs);
            $result = $tab->id;
        }
        return $result;
    }

    public function display($content=null, $title=null, $message=null)
    {

        $itemname   = $this->getItemname();
        $currentTab = $this->getCurrentTab();
        $tabs       = $this->getTabs();
        $panel      = $this->getPanel();
        $module     = $this->getModule();

        if (empty($content)) {
            $content    = $this->getContent();
        } else {
            $settpl['TITLE']   = & $title;
            $settpl['MESSAGE'] = & $message;
            $settpl['CONTENT'] = & $content;
            $content = PHPWS_Template::process($settpl, 'controlpanel', 'default.tpl');
        }

        if (!isset($module)) {
            $module = 'controlpanel';
        }

        if (!isset($panel)) {
            $panel = CP_DEFAULT_PANEL;
        }

        if (!is_file(PHPWS_Template::getTemplateDirectory($module) . $panel)){
            $module = 'controlpanel';
            $panel = CP_DEFAULT_PANEL;
        }

        foreach ($tabs as $id=>$tab) {
            if ($this->_secure) {
                $tab->enableSecure();
            } else {
                $tab->disableSecure();
            }
            $tpl['TITLE'] = $tab->getLink();
            if ($id == $currentTab){
                $tpl['STATUS'] = 'class="active"';
                $tpl['ACTIVE'] = ' ';
            }
            else {
                $tpl['STATUS'] = 'class="inactive"';
                $tpl['INACTIVE'] = ' ';
            }
            $template['tabs'][] = $tpl;
        }

        $template['CONTENT'] = $content;
        return PHPWS_Template::process($template, $module, $panel);
    }
}

?>