<?php

class Access {

    function main()
    {
        $content = NULL;

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

        switch ($command) {
        case 'rewrite':
            PHPWS_Core::initModClass('access', 'Forms.php');
            $title = _('Mod_Rewrite');
            $content = Access_Forms::rewrite();
            break;

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

        case 'post_shortcut':
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

        case 'write_htaccess':
            Access::writeAccess();
            break;
        }

        $tpl['TITLE'] = $title;
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
            $result = Access::writeAccess();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = _('A serious error occurred. Please check your error.log.');
                $content[] = sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], _('Return to previous page.'));
            } else {
                $content[] = _('Shortcut saved successfully!');
                $content[] = _('You can now reference this page with this following link:');
                $content[] = $shortcut->getRewrite(TRUE);
            }
        }
        return implode('<br />', $content);
    }

    function check_htaccess()
    {
        return is_writable('.htaccess');
    }

    function writeAccess()
    {
        if (!Current_User::authorized('access')) {
            Current_User::disallow();
            exit();
        }

        $content = Access::getRewrite();

        echo $content;

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
            $content = Access::getShortcuts();
            $content[] = 'RewriteEngine On';
            $content[] = 'Options +FollowSymlinks';
            $content[] = '';
            $content[] = PHPWS_Settings::get('access', 'default_rewrite_1');
            $content[] = PHPWS_Settings::get('access', 'default_rewrite_2');

            return implode("\n", $content);
        } else {
            return "RewriteEngine Off";
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

        $link['title'] = _('Rewrite');
        $tabs['rewrite'] = $link;

        if (Current_User::allow('deny_allow')) {
            $link['title'] = _('Deny/Allow');
            $tabs['deny_allow'] = $link;
        }


        $panel = & new PHPWS_Panel('access_panel');
        $panel->enableSecure();
        $panel->quickSetTabs($tabs);

        $panel->setModule('access');
        return $panel;

    }

    function getShortcuts()
    {
        $shortcuts = array();

        $db = & new PHPWS_DB('access_shortcuts');
        $result = $db->select();
        if (empty($result)) {
            return $shortcuts;
        } elseif (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return $shortcuts;
        } else {
            foreach ($result as $sc) {
                $shortcuts[] = sprintf('RewriteRule ^%s.html$ %s[L]', $keyword, $url);
            }
            return $shortcuts;
        }
    }

}