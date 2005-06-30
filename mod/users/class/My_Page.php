<?php

class My_Page {
    var $modules = NULL;

    function main()
    {
        $result = $this->init();

        if (PEAR::isError($result)){
            log($result);
            return _('The is a problem with My Page.');
        }

        $panel = & My_Page::cpanel();

        $module = $panel->getCurrentTab();

        if (!$this->moduleIsRegistered($module)){
            Layout::add(_('This module is not registered with My Page'));
            return;
        }

        $content = My_Page::userOption($module);

        $panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    function init()
    {
        PHPWS_Core::initCoreClass('Module.php');
        $db = & new PHPWS_DB('users_my_page_mods');
        $db->addColumn('mod_title');
        $result = $db->select('col');

        if (PEAR::isError($result))
            return $result;

        foreach ($result as $mod_title)
            $this->modules[$mod_title] = & new PHPWS_Module($mod_title);
    }

    function moduleIsRegistered($module)
    {
        return in_array($module, array_keys($this->modules));
    }

    function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=users&amp;action=user';

        foreach ($this->modules as $module){
            $link = 'index.php?module=users&amp;action=user';
            $tabs[$module->getTitle()] = array('title'=>$module->getProperName(), 'link'=>$link);
        }

        $panel = & new PHPWS_Panel('users');
        $panel->quickSetTabs($tabs);
        $panel->setModule('users');
        $panel->setPanel('panel.tpl');
        return $panel;
    }

    function userOption($module_title)
    {
        $module = & new PHPWS_Module($module_title);
        $directory = $module->getDirectory();

        $final_file = $directory . 'inc/my_page.php';

        if (!is_file($final_file)){
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'users', 'userOption', $final_file);
            return _('There was a problem with this module\'s My Page file.');
        }

        include $final_file;
        if (!function_exists('my_page'))
            exit('Missing my page in userOption My_Page.php');

        $content = my_page();
        return $content;
    }

    function registerMyPage($mod_title)
    {
        $filename = sprintf('mod/%s/inc/my_page.php', $mod_title);

        if (!is_file($filename))
            return FALSE;

        $db = & new PHPWS_DB('users_my_page_mods');
        $db->addValue('mod_title', $mod_title);
        return $db->insert();
    }
}

?>