<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class My_Page {
    public $modules = NULL;

    public function main()
    {
        $auth = Current_User::getAuthorization();

        if (!Current_User::isLogged() || !$auth->local_user ) {
            PHPWS_Core::errorPage('403');
        }

        $result = $this->init();

        if (PHPWS_Error::isError($result)){
            PHPWS_Error::log($result);
            Layout::add(PHPWS_ControlPanel::display(dgettext('users', 'The is a problem with My Page.')));
            return;
        } elseif (!$result) {
            Layout::add(PHPWS_ControlPanel::display(dgettext('users', 'No modules are registered to My Page.')));
            return;
        }

        $panel = My_Page::cpanel();

        $module = $panel->getCurrentTab();

        if (!$this->moduleIsRegistered($module)){
            Layout::add(dgettext('users', 'This module is not registered with My Page'));
            return;
        }

        $content = My_Page::userOption($module);

        if (PHPWS_Error::isError($content)) {
            $panel->setContent($content->getMessage());
        } else {
            $panel->setContent($content);
        }
        Layout::add(PHPWS_ControlPanel::display($panel->display(), 'my_page'));
    }

    public function init()
    {
        PHPWS_Core::initCoreClass('Module.php');
        $db = new PHPWS_DB('users_my_page_mods');
        $db->addColumn('mod_title');
        $result = $db->select('col');

        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        if ($result) {
            foreach ($result as $mod_title) {
                $this->modules[$mod_title] = new PHPWS_Module($mod_title);
            }
        } else {
            return FALSE;
        }
        return TRUE;
    }

    public function moduleIsRegistered($module)
    {
        return in_array($module, array_keys($this->modules));
    }

    public function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=users&amp;action=user';

        foreach ($this->modules as $module){
            $link = 'index.php?module=users&amp;action=user';
            $tabs[$module->title] = array('title'=>$module->getProperName(), 'link'=>$link);
        }

        $panel = new PHPWS_Panel('users');
        $panel->quickSetTabs($tabs);
        $panel->setModule('users');
        $panel->setPanel('panel.tpl');
        return $panel;
    }

    public function userOption($module_title)
    {
        $module = new PHPWS_Module($module_title);
        $directory = $module->getDirectory();

        $final_file = $directory . 'inc/my_page.php';

        if (!is_file($final_file)){
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'users', 'userOption', $final_file);
            return dgettext('users', 'There was a problem with this module\'s My Page file.');
        }

        include $final_file;
        if (!function_exists('my_page')) {
            return PHPWS_Error::get(USER_MISSING_MY_PAGE, 'users', 'My_Page::userOption', $module_title);
        }

        $content = my_page();
        return $content;
    }

    public static function registerMyPage($mod_title)
    {
        $filename = sprintf('%smod/%s/inc/my_page.php', PHPWS_SOURCE_DIR, $mod_title);
        if (!is_file($filename)) {
            return FALSE;
        }

        $db = new PHPWS_DB('users_my_page_mods');
        $db->addValue('mod_title', $mod_title);
        return $db->insert();
    }

    public static function unregisterMyPage($mod_title)
    {
        $db = new PHPWS_DB('users_my_page_mods');
        $db->addWhere('mod_title', $mod_title);
        return $db->delete();
    }


    public static function addHidden(PHPWS_Form $form, $module)
    {
        $form->addHidden('module', 'users');
        $form->addHidden('action', 'user');
        $form->addHidden('tab', $module);
    }
}

?>