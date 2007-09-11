<?php

/**
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::requireConfig('controlpanel');
PHPWS_Core::initModClass('controlpanel', 'Tab.php');

class PHPWS_Panel{
    var $itemname     = null;
    var $tabs         = null;
    var $content      = null;
    var $module       = null;
    var $panel        = null;
    var $_secure      = false;

    function PHPWS_Panel($itemname=null)
    {
        if (isset($itemname)) {
            $this->setItemname($itemname);
        }
    }

    function disableSecure()
    {
        $this->_secure = false;
    }

    function enableSecure()
    {
        $this->_secure = true;
    }

    function quickSetTabs($tabs)
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

            $tab->setOrder($count);
            $count++;
            $this->tabs[$id] = $tab;
        }

        return true;
    }


    function setTabs($tabs)
    {
        if (!is_array($tabs)) {
            return PHPWS_Error::get(CP_BAD_TABS, 'controlpanel', 'setTabs');
        }
      
        $this->tabs = $tabs;
    }

    function getTabs()
    {
        return $this->tabs;
    }

    function dropTab($id)
    {
        unset($this->tabs[$id]);
    }

    function setContent($content)
    {
        $this->content = $content;
    }

    function getContent()
    {
        return $this->content;
    }

    function setItemname($itemname)
    {
        $this->itemname = $itemname;
    }

    function getItemname()
    {
        return $this->itemname;
    }


    function setModule($module)
    {
        $this->module = $module;
    }

    function getModule()
    {
        return $this->module;
    }

    function setPanel($panel)
    {
        $this->panel = $panel;
    }

    function getPanel()
    {
        return $this->panel;
    }

    function setCurrentTab($tab)
    {
        $itemname = $this->getItemname();
        $_SESSION['Panel_Current_Tab'][$itemname] = $tab;
    }

    function getCurrentTab()
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

    function getFirstTab()
    {
        PHPWS_Core::initModClass('controlpanel', 'Tab.php');
        $result = null;

        $tabs = $this->getTabs();

        if (isset($tabs)){
            $tab = array_shift($tabs);
            $result = $tab->id;
        }
        return $result;
    }

    function display()
    {
        $itemname   = $this->getItemname();
        $currentTab = $this->getCurrentTab();
        $tabs       = $this->getTabs();
        $panel      = $this->getPanel();
        $module     = $this->getModule();
        $content    = $this->getContent();

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