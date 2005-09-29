<?php

PHPWS_Settings::load('access');

PHPWS_Core::requireConfig('access');

class Access {

    function main()
    {
        $title = $content = NULL;

        $message = Access::getMessage();

        if (!Current_User::allow('access')) {
            Current_User::disallow();
            exit();
        }

        $panel = Access::cpanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        // If the command is empty, that means no tabs were set
        // In this case, an admin with full rights needs to log in
        if (empty($command)) {
            $title = _('Sorry');
            $content = _('Access needs a higher administrator\'s attention before you may use it.');
        } else {
            switch ($command) {
            case 'post_admin':
                Access::saveAdmin();
                Access::sendMessage(_('Settings saved.'), 'admin');
                break;

            case 'admin':
                PHPWS_Core::initModClass('access', 'Forms.php');
                $title = _('Administrator');
                $content = Access_Forms::administrator();
                break;

            case 'deny_allow':
                PHPWS_Core::initModClass('access', 'Forms.php');
                $title = _('Denys and Allows');
                $content = Access_Forms::denyAllowForm();
                break;

            case 'disable_shortcut':
                unset($_SESSION['Access_Shortcut_Enabled']);
                $message = _('Shortcuts disabled.');
            case 'shortcuts':
                PHPWS_Core::initModClass('access', 'Forms.php');
                $title = _('Shortcuts');
                $content = Access_Forms::shortcuts();
                break;

            case 'enable_shortcut':
                $title = _('Shortcut menu enabled!');
                $content = _('To create a shortcut, browse to a Shortcut enabled page.');
                $_SESSION['Access_Shortcut_Enabled'] = TRUE;
                break;

            case 'post_update_file':
                $result = Access::writeAccess();
                if ($result) {
                    $message = _('.htaccess file written.');
                } else {
                    $message = _('Unable to save .htaccess file.');
                }
                Access::sendMessage($message, 'update');
                break;

            case 'post_shortcut_list':
                $message = NULL;
                $result = Access::postShortcutList();
                if (PEAR::isError($result)) {
                    $message = _('An error occurred. Please check your logs.');
                }
                Access::sendMessage($message, 'shortcuts');
                break;

            case 'update':
                PHPWS_Core::initModClass('access', 'Forms.php');
                $title = _('Update .htaccess file');
                $content = Access_Forms::updateFile();
                break;

            case 'post_shortcut':
                if (isset($_POST['off'])) {
                    unset($_SESSION['Access_Shortcut_Enabled']);
                    PHPWS_Core::goBack();
                    exit();
                }
                PHPWS_Core::initModClass('access', 'Shortcut.php');
                $title = _('Adding Shortcut');
                $shortcut = & new Access_Shortcut;
                $result = $shortcut->postShortcut();
                if (PEAR::isError($result)) {
                    $content = _('An error occurred:') . '<br />' . $result->getMessage() . '<br />';
                    $content .= sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], _('Return to previous page.'));
                } elseif ($result == FALSE) {
                    $content = _('A serious error occurred. Please check your error.log.') . '<br />';
                    $content .= sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], _('Return to previous page.'));
                } else {
                    $content = Access::saveShortcut($shortcut);
                }
                break;

            }
        }

        $tpl['TITLE']   = $title;
        $tpl['MESSAGE'] = $message;
        $tpl['CONTENT'] = $content;
        $main = PHPWS_Template::process($tpl, 'access', 'main.tpl');

        $panel->setContent($main);
        $finalPanel = $panel->display();
        
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function saveShortcut(&$shortcut)
    {
        $result = $shortcut->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = _('A serious error occurred. Please check your error.log.');
            $content[] = sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], _('Return to previous page.'));
        } else {
            if (PHPWS_Settings::get('access', 'allow_file_update')) {
                $result = Access::writeAccess();
                if (!$result) {
                    $content[] = _('An error occurred. Please check your error.log.');
                    $content[] = sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], _('Return to previous page.'));
                } else {
                    $content[] = _('Shortcut saved successfully!');
                    $content[] = _('You can now reference this page with this following link:');
                    $content[] = $shortcut->getRewrite(TRUE);
                }
            } else {
                $content[] = _('Access has saved your shortcut.');
                $content[] = _('An administrator will need to approve it before it is functional.');
                $content[] = _('When accepted, you will be able to use the following link:');
                $content[] = $shortcut->getRewrite(TRUE, FALSE);
            }
        }
        return implode('<br />', $content);
    }

    function saveAdmin()
    {
        if (isset($_POST['shortcuts_enabled'])) {
            PHPWS_Settings::set('access', 'shortcuts_enabled', 1);
        } else {
            PHPWS_Settings::set('access', 'shortcuts_enabled', 0);
        }
        
        if (isset($_POST['rewrite_engine'])) {
            PHPWS_Settings::set('access', 'rewrite_engine', 1);
        } else {
            PHPWS_Settings::set('access', 'rewrite_engine', 0);
        }

        if (isset($_POST['allow_file_update'])) {
            PHPWS_Settings::set('access', 'allow_file_update', 1);
        } else {
            PHPWS_Settings::set('access', 'allow_file_update', 0);
        }

        PHPWS_Settings::save('access');
    }

    function check_htaccess()
    {
        return is_writable('.htaccess');
    }

    function writeAccess()
    {
        if (!PHPWS_Settings::get('access', 'allow_file_update') && 
            !Current_User::authorized('access', 'admin_options')) {
            Current_User::disallow();
            exit();
        }

        $content =  Access::getRewrite() . "\n";

        if (!is_writable('files/access/')) {
            PHPWS_Error::log(ACCESS_FILES_DIR, 'access', 'Access::writeAccess'); 
            return FALSE;
        }
        
        if (!is_file('.htaccess')) {
            PHPWS_Error::log(ACCESS_HTACCESS_MISSING, 'access', 'Access::writeAccess');
            return FALSE;
        }

        if (!@copy('./.htaccess', './files/access/htaccess_' . mktime())) {
            PHPWS_Error::log(ACCESS_FILES_DIR, 'access', 'Access::writeAccess'); 
            return FALSE;
        }


        $result = @file_put_contents('.htaccess', $content);
        if (!$result) {
            PHPWS_Error::log(ACCESS_HTACCESS_WRITE, 'access', 'Access::writeAccess'); 
            return FALSE;
        }

        return TRUE;
    }


    function shortcut()
    {
        if (!isset($_SESSION['Access_Shortcut_Enabled'])) {
            return;
        }

        PHPWS_Core::initModClass('access', 'Forms.php');
        Access_Forms::shortcut_menu();
    }


    function getRewrite()
    {
        if (PHPWS_Settings::get('access', 'rewrite_engine')) {
            $content[] = 'RewriteEngine On';
            $content[] = 'Options +FollowSymlinks';
            $content[] = '';
            $content[] = Access::listShortcuts();
            $content[] = DEFAULT_REWRITE_1;
            $content[] = DEFAULT_REWRITE_2;

            return implode("\n", $content) . "\n";
        } else {
            return "RewriteEngine Off\n";
        }
    }

    function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=access';

        if (MOD_REWRITE_ENABLED && Access::check_htaccess() &&
            PHPWS_Settings::get('access', 'rewrite_engine')) {
            $link['title'] = _('Shortcuts');
            $tabs['shortcuts'] = $link;
        }

        if (Current_User::allow('access', 'deny_allow') && Access::check_htaccess()) {
            $link['title'] = _('Deny/Allow');
            $tabs['deny_allow'] = $link;
        }

        if (Current_User::allow('access', 'admin_options')) {
            $link['title'] = _('Administrator');
            $tabs['admin'] = $link;

            $link['title'] = _('Update');
            $tabs['update'] = $link;

        }

 
        $panel = & new PHPWS_Panel('access_panel');
        $panel->enableSecure();

        if (!empty($tabs)) {
            $panel->quickSetTabs($tabs);
        }

        $panel->setModule('access');
        return $panel;

    }

    function listShortcuts()
    {
        $shortcuts = Access::getShortcuts(TRUE);

        if (PEAR::isError($shortcuts)) {
            PHPWS_Error::log($shortcuts);
            return NULL;
        } elseif (empty($shortcuts)) {
            return NULL;
        } else {
            foreach ($shortcuts as $sc) {
                $sc_list[] = $sc->getHtaccess();
            }
            return implode("\n", $sc_list);
        }

    }

    function getAllowDeny()
    {
        $db = & new PHPWS_DB('access_allow_deny');
        $db->addOrder('ip_address');
        return $db->getObjects('Access_Allow_Deny');
    }

    function getShortcuts($accepted_only=FALSE)
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $db = & new PHPWS_DB('access_shortcuts');
        $db->addOrder('keyword');
        if ($accepted_only) {
            $db->addWhere('accepted', 1);
        }
        return $db->getObjects('Access_Shortcut');
    }

    function sendMessage($message, $command)
    {
        $_SESSION['Access_message'] = $message;
        PHPWS_Core::reroute(sprintf('index.php?module=access&command=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Access_message'])) {
            $message = $_SESSION['Access_message'];
        }
        unset($_SESSION['Access_message']);
        return $message;
    }

    function postShortcutList()
    {
        if ($_POST['list_action'] == 'none' || empty($_POST['shortcut'])) {
            return NULL;
        }

        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $db = & new PHPWS_DB('access_shortcuts');
        $db->addWhere('id', $_POST['shortcut']);

        if ($_POST['list_action'] == 'accept') {
            $db->addWhere('accepted', 0);
        }

        $result = $db->getObjects('Access_Shortcut');

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!empty($result)) {
            foreach ($result as $sc) {
                if ($_POST['list_action'] == 'accept') {
                    $sc->accepted = 1;
                    $result = $sc->save();
                } elseif ($_POST['list_action'] == 'delete') {
                    $result = $sc->delete();
                }

                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        return TRUE;
    }
}