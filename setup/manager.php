<?php
/**
 * Stand-alone manager for fallout
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

// need to add ip restriction

header('Content-Type: text/html; charset=UTF-8');

if (!is_file('../config/core/config.php')) {
    echo _('Your core config.php file is missing. Please create one and return.');
    exit();
}
include '../config/core/config.php';
require_once PHPWS_SOURCE_DIR . 'core/class/Init.php';

PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initCoreClass('Template.php');
require_once PHPWS_SOURCE_DIR . 'inc/Functions.php';

PHPWS_Core::initModClass('users', 'Current_User.php');
PHPWS_Core::initModClass('boost', 'Boost.php');
PHPWS_Core::initModClass('layout', 'Layout.php');

session_start();

PHPWS_SiteManager::admin();

/**
 * Manager class
 */
class PHPWS_SiteManager
{

    function admin()
    {
        $title = $content = $message = NULL;

        if (!Current_User::isLogged() && !isset($_POST['phpws_username'])) {
            $command = 'login';
        } elseif (!Current_User::isDeity() && !isset($_POST['phpws_username'])) {
            PHPWS_Core::killAllSessions();
            $command = 'login';
        } elseif (!isset($_REQUEST['command'])) {
            $command = 'main';
        } else {
            $command = &$_REQUEST['command'];
        }

        switch ($command) {
            case 'login':
                $title = _('Login to Site Manager');
                $content = PHPWS_SiteManager::loginForm();
                break;

            case 'post_login':
                if (!Current_User::loginUser($_POST['phpws_username'], $_POST['phpws_password'])) {
                    $title = _('Login page');
                    $message = _('Username and password combination not found.');
                    $content = PHPWS_SiteManager::loginForm();
                } elseif (!Current_User::isDeity()) {
                    PHPWS_Core::killAllSessions();
                    $content = _('You must be a deity to run Site Manager.');
                } else {
                    PHPWS_Core::reroute('manager.php?command=main');
                }

                break;

            case 'main':
                $title = _('Main Menu');
                $content = PHPWS_SiteManager::main();
                break;

            case 'uninstall_module':
                $boost = & new PHPWS_Boost;
                $module = & new PHPWS_Module($_GET['module_title']);
                $boost->addModule($module);
                $boost->uninstall();
                PHPWS_Core::goBack();
                break;
        }

        $tpl['STYLE1'] = '../themes/default/style.css';
        $tpl['STYLE2'] = '../themes/default/default.css';

        $tpl['MESSAGE'] = $message;
        $tpl['TITLE']   = $title;
        $tpl['CONTENT'] = $content;

        echo PHPWS_Template::process($tpl, 'core', 'manager.tpl');
    }

    function loginForm()
    {
        $form = & new PHPWS_Form;
        $form->setAction('manager.php');
        $form->addHidden('command', 'post_login');
        $form->addText('phpws_username');
        $form->setLabel('phpws_username', _('Username'));
        $form->addPassword('phpws_password');
        $form->setLabel('phpws_password', _('Password'));
        $form->addSubmit(_('Log in'));
        $tpl = $form->getTemplate();

        $tpl['INTRO'] = _('Please login');

        return PHPWS_Template::process($tpl, 'core', 'login.tpl');

    }

    function main()
    {
        $db = & new PHPWS_DB('modules');
        $db->addOrder('title');
        $result = $db->select();

        foreach ($result as $module) {
            $link[1] = sprintf('<a href="manager.php?command=uninstall_module&amp;module_title=%s">%s</a>', $module['title'], _('Uninstall'));
            $tpl['module_row'][] = array('TITLE'  => $module['title'],
                                         'ACTION' => implode(' | ', $link));
        }

        $tpl['INSTALL_TITLE'] = _('Installed modules');
        $tpl['TITLE_LABEL']   = _('Module title');
        $tpl['ACTION_LABEL']  = _('Action');

        $content = PHPWS_Template::process($tpl, 'core', 'module_list.tpl');

        return $content;
    }

}

?>