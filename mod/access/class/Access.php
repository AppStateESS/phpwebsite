<?php

/**
 * Main administrative control class for Access
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
define('SHORTCUT_BAD_KEYWORD', 1);
define('SHORTCUT_WORD_IN_USE', 2);
define('SHORTCUT_MISSING_KEYWORD', 3);
define('SHORTCUT_MISSING_URL', 4);
define('ACCESS_FILES_DIR', 5);
define('ACCESS_HTACCESS_WRITE', 6);
define('ACCESS_HTACCESS_MISSING', 7);

PHPWS_Core::requireConfig('access');

class Access {

    public static function main()
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
            $title = dgettext('access', 'Sorry');
            $content = dgettext('access', 'Access needs a higher administrator\'s attention before you may use it.');
        } else {
            switch ($command) {
                case 'post_admin':
                    Access::saveAdmin();
                    Access::sendMessage(dgettext('access', 'Settings saved.'), 'admin');
                    break;

                case 'restore_default':
                    $source = PHPWS_SOURCE_DIR . 'core/inc/htaccess';
                    $dest = PHPWS_HOME_DIR . '.htaccess';
                    if (@copy($source, $dest)) {
                        Access::sendMessage(dgettext('access', 'Default .htaccess file restored.'), 'update');
                    } else {
                        Access::sendMessage(dgettext('access', 'Unable to restore default .htaccess file.'), 'update');
                    }
                    break;

                case 'post_deny_allow':
                    $result = Access::postDenyAllow();
                    if ($result == false) {
                        Access::sendMessage(dgettext('access', 'IP address was not formatted correctly or not allowed.'), 'deny_allow');
                    } elseif (PHPWS_Error::isError($result)) {
                        PHPWS_Error::log($result);
                        Access::sendMessage(dgettext('access', 'An error occurred.') . ' ' . dgettext('access', 'Please check your logs.'), 'deny_allow');
                    }
                    Access::sendMessage(NULL, 'deny_allow');
                    break;

                case 'delete_allow_deny':
                    PHPWS_Core::initModClass('access', 'Allow_Deny.php');
                    $allow_deny = new Access_Allow_Deny($_GET['ad_id']);
                    $allow_deny->delete();
                    Access::sendMessage(dgettext('access', 'IP address deleted.'), 'deny_allow');
                    break;
                case 'deny_allow':
                    PHPWS_Core::initModClass('access', 'Forms.php');
                    $title = dgettext('access', 'Denys and Allows');
                    $content = Access_Forms::denyAllowForm();
                    break;

                case 'delete_shortcut':
                    PHPWS_Core::initModClass('access', 'Shortcut.php');
                    $shortcut = new Access_Shortcut($_REQUEST['shortcut_id']);
                    if (empty($shortcut->_error) && $shortcut->id) {
                        $result = $shortcut->delete();
                        if (PHPWS_Error::isError($result)) {
                            Access::sendMessage(dgettext('access', 'An error occurred when deleting your shortcut.'), 'shortcuts');
                        }
                    }
                    Access::sendMessage(dgettext('access', 'Shortcut deleted'), 'shortcuts');
                    break;

                case 'shortcuts':
                    PHPWS_Core::initModClass('access', 'Forms.php');
                    $title = dgettext('access', 'Shortcuts');
                    $content = Access_Forms::shortcuts();
                    break;


                case 'post_shortcut_list':
                    $message = NULL;
                    $result = Access::postShortcutList();
                    if (PHPWS_Error::isError($result)) {
                        $message = dgettext('access', 'An error occurred.') . ' ' . dgettext('access', 'Please check your logs.');
                    }
                    Access::sendMessage($message, 'shortcuts');
                    break;

                case 'edit_shortcut':
                    PHPWS_Core::initModClass('access', 'Forms.php');
                    $content = Access_Forms::shortcut_menu();
                    Layout::nakedDisplay($content);
                    exit();
                    break;

                case 'post_shortcut':
                    PHPWS_Core::initModClass('access', 'Shortcut.php');

                    if (isset($_POST['sc_id'])) {
                        $shortcut = new Access_Shortcut($_POST['sc_id']);
                    } else {
                        $shortcut = new Access_Shortcut;
                    }

                    $result = $shortcut->postShortcut();
                    $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="window.close()" />', dgettext('access', 'Close window'));
                    if (PHPWS_Error::isError($result)) {
                        PHPWS_Core::initModClass('access', 'Forms.php');
                        $message = $result->getMessage();
                        $content = Access_Forms::shortcut_menu();
                    } elseif ($result == false) {
                        $tpl['TITLE'] = dgettext('access', 'A serious error occurred. Please check your error.log.') . '<br />';
                        $tpl['CONTENT'] = sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], dgettext('access', 'Return to previous page.'));
                        $content = PHPWS_Template::process($tpl, 'access', 'box.tpl');
                    } else {
                        $content = Access::saveShortcut($shortcut);
                    }

                    $tpl['MESSAGE'] = $message;
                    $tpl['CONTENT'] = $content;

                    Layout::nakedDisplay(PHPWS_Template::process($tpl, 'access', 'main.tpl'));
                    break;

                case 'htaccess':
                    if (Current_User::isDeity()) {
                        $title = dgettext('access', 'htaccess');
                        $content = Access::htaccess();
                    } else {
                        Current_User::disallow();
                    }
                    break;

                case 'add_rewritebase':
                    if (Current_User::isDeity()) {
                        Access::addRewriteBase();
                        PHPWS_Core::goBack();
                    } else {
                        Current_User::disallow();
                    }
                    break;

                case 'add_forward':
                    if (Current_User::isDeity()) {
                        Access::addForward();
                        PHPWS_Core::goBack();
                    } else {
                        Current_User::disallow();
                    }
                    break;

                case 'remove_forward':
                    if (Current_User::isDeity()) {
                        Access::removeForward();
                        PHPWS_Core::goBack();
                    } else {
                        Current_User::disallow();
                    }
                    break;

                case 'menu_fix':
                    Access::menuFix();
                    PHPWS_Core::goBack();
                    break;

                case 'page_fix':
                    Access::pageFix();
                    PHPWS_Core::goBack();
                    break;

                case 'autoforward_on':
                    PHPWS_Settings::set('access', 'forward_ids', 1);
                    PHPWS_Settings::save('access');
                    PHPWS_Core::goBack();
                    break;

                case 'autoforward_off':
                    PHPWS_Settings::set('access', 'forward_ids', 0);
                    PHPWS_Settings::save('access');
                    PHPWS_Core::goBack();
                    break;
            }
        }

        $tpl['TITLE'] = $title;
        $tpl['MESSAGE'] = $message;
        $tpl['CONTENT'] = $content;

        $main = PHPWS_Template::process($tpl, 'access', 'main.tpl');

        $panel->setContent($main);
        $finalPanel = $panel->display();

        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    public static function pageFix()
    {
        $db = new PHPWS_DB('ps_page');
        $db->addColumn('id');
        $db->addColumn('title');
        $db->setIndexBy('id');
        $all_pages = $db->select('col');

        if (empty($all_pages)) {
            return;
        }

        $db2 = new PHPWS_DB('access_shortcuts');
        $db2->addWhere('url', 'pagesmith:%', 'like');
        $db2->addColumn('url');
        $all_shortcuts = $db2->select('col');

        $current_page_ids = array();
        if (!empty($all_shortcuts)) {
            foreach ($all_shortcuts as $page) {
                $sc_array = explode(':', $page);
                $current_page_ids[] = array_pop($sc_array);
            }
        }
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        foreach ($all_pages as $id => $title) {
            if (in_array($id, $current_page_ids)) {
                continue;
            }

            $shortcut = new Access_Shortcut;
            $shortcut->setKeyword($title);
            $shortcut->url = 'pagesmith:' . $id;
            $shortcut->active = 1;
            $shortcut->save();
        }
    }

    public static function saveShortcut(Access_Shortcut $shortcut)
    {
        $result = $shortcut->save();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = dgettext('access', 'A serious error occurred. Please check your error.log.');
            $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="window.close()" />', dgettext('access', 'Close window'));
        } else {
            $tpl['TITLE'] = dgettext('access', 'Access has saved your shortcut.');
            $content[] = dgettext('access', 'You can access this item with the following link:');
            $url = $shortcut->getRewrite(true, false);
            $content[] = $url;
            $js['location'] = $url;
            javascript('close_refresh', $js);
            $tpl['CLOSE'] = sprintf('<input type="button" value="%s" onclick="closeWindow(); return false" />', dgettext('access', 'Close window'));
        }
        $tpl['CONTENT'] = implode('<br />', $content);

        return PHPWS_Template::process($tpl, 'access', 'box.tpl');
    }

    public function getAllowDenyList()
    {
        $content = array();
        PHPWS_Core::initModClass('access', 'Allow_Deny.php');

        if (!PHPWS_Settings::get('access', 'allow_deny_enabled')) {
            return "Order Allow,Deny\nAllow from all\n\n";
        }

        $deny_all = PHPWS_Settings::get('access', 'deny_all');
        $allow_all = PHPWS_Settings::get('access', 'allow_all');

        $deny_str = $allow_str = NULL;

        if ($deny_all && $allow_all) {
            return NULL;
        } elseif ($deny_all) {
            $deny_str = 'Deny from all';
        } elseif ($allow_all) {
            $allow_str = 'Allow from all';
        }

        $db = new PHPWS_DB('access_allow_deny');
        $db->addWhere('active', 1);

        if ($deny_all) {
            $db->addWhere('allow_or_deny', 1);
        } elseif ($allow_all) {
            $db->addWhere('allow_or_deny', 0);
        }

        $result = $db->getObjects('Access_Allow_Deny');

        if ($deny_all) {
            $content[] = 'Order Deny,Allow';
            $content[] = $deny_str;
            $content[] = 'Allow from 127.0.0.1';
            $content[] = 'Allow from ' . Current_User::getIP();

            if (!empty($result)) {
                foreach ($result as $ad) {
                    $content[] = 'Allow from ' . $ad->ip_address;
                }
            }
        } elseif ($allow_all) {
            $content[] = 'Order Allow,Deny';
            $content[] = $allow_str;

            if (!empty($result)) {
                foreach ($result as $ad) {
                    $content[] = 'Deny from ' . $ad->ip_address;
                }
            }
        } else {
            if (!empty($result)) {
                $content[] = 'Order Deny,Allow';
                foreach ($result as $ad) {
                    if ($ad->allow_or_deny) {
                        $allows[] = 'Allow from ' . $ad->ip_address;
                    } else {
                        $denys[] = 'Deny from ' . $ad->ip_address;
                    }
                }

                if (!empty($denys)) {
                    $content[] = implode("\n", $denys);
                }

                if (!empty($allows)) {
                    $content[] = implode("\n", $allows);
                }
            }
        }

        return implode("\n", $content) . "\n\n";
    }

    public function loadShortcut($title)
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $shortcut = new Access_Shortcut;
        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('keyword', $title);
        $db->setLimit(1);
        if (!$db->loadObject($shortcut)) {
            return;
        }
    }

    public static function shortcut(Key $key)
    {
        $vars['command'] = 'edit_shortcut';
        $vars['key_id'] = $key->id;
        $link = PHPWS_Text::linkAddress('access', $vars, true);
        $js_vars['address'] = $link;
        $js_vars['label'] = dgettext('access', 'Shortcut');
        $js_vars['height'] = '200';
        $js_link = javascript('open_window', $js_vars);
        MiniAdmin::add('access', $js_link);
    }

    public static function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link['link'] = 'index.php?module=access';

        if (MOD_REWRITE_ENABLED) {
            $link['title'] = dgettext('access', 'Shortcuts');
            $tabs['shortcuts'] = $link;
        }

        if (Current_User::allow('access', 'admin_options')) {
            $link['title'] = dgettext('access', 'Allow/Deny');
            $tabs['deny_allow'] = $link;
        }

        if (Current_User::isDeity()) {
            $link['title'] = dgettext('access', '.htaccess');
            $tabs['htaccess'] = $link;
        }

        $panel = new PHPWS_Panel('access_panel');
        $panel->enableSecure();

        if (!empty($tabs)) {
            $panel->quickSetTabs($tabs);
        }

        $panel->setModule('access');
        return $panel;
    }

    public static function getAllowDeny()
    {
        $db = new PHPWS_DB('access_allow_deny');
        $db->addOrder('ip_address');
        return $db->getObjects('Access_Allow_Deny');
    }

    public function getShortcuts($active_only = false)
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $db = new PHPWS_DB('access_shortcuts');
        $db->addOrder('keyword');
        if ($active_only) {
            $db->addWhere('active', 1);
        }
        return $db->getObjects('Access_Shortcut');
    }

    public static function sendMessage($message, $command)
    {
        $_SESSION['Access_message'] = $message;
        PHPWS_Core::reroute(sprintf('index.php?module=access&command=%s&authkey=%s', $command, Current_User::getAuthKey()));
        exit();
    }

    public static function getMessage()
    {
        $message = NULL;
        if (isset($_SESSION['Access_message'])) {
            $message = $_SESSION['Access_message'];
        }
        unset($_SESSION['Access_message']);
        return $message;
    }

    public static function postShortcutList()
    {
        if (!Current_User::authorized('access')) {
            Current_User::disallow();
            exit();
        }

        if ($_POST['list_action'] == 'none' || empty($_POST['shortcut'])) {
            return NULL;
        }

        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('id', $_POST['shortcut']);

        switch ($_POST['list_action']) {
            case 'active':
                $db->addValue('active', 1);
                $result = $db->update();
                break;

            case 'deactive':
                $db->addValue('active', 0);
                $result = $db->update();
                break;

            case 'delete':
                $result = $db->delete();
                break;
        }

        if (PHPWS_Error::isError($result)) {
            return $result;
        }
    }

    public static function postDenyAllow()
    {
        if (!Current_User::authorized('access', 'admin_options')) {
            Current_User::disallow();
            exit();
        }

        PHPWS_Core::initModClass('access', 'Allow_Deny.php');

        if (@$_POST['allow_deny_enabled']) {
            PHPWS_Settings::set('access', 'allow_deny_enabled', 1);
        } else {
            PHPWS_Settings::set('access', 'allow_deny_enabled', 0);
        }

        PHPWS_Settings::save('access');

        if (isset($_POST['add_allow_address']) && !empty($_POST['allow_address'])) {
            $allow = new Access_Allow_Deny;
            $allow->allow_or_deny = 1;
            $result = $allow->setIpAddress($_POST['allow_address']);
            if (!$result) {
                return $result;
            }

            $allow->active = 1;
            return $allow->save();
        }

        if (isset($_POST['add_deny_address']) && !empty($_POST['deny_address'])) {
            $deny = new Access_Allow_Deny;
            $deny->allow_or_deny = 0;
            $result = $deny->setIpAddress($_POST['deny_address']);
            if (!$result) {
                return $result;
            }

            $deny->active = 1;
            return $deny->save();
        }

        if (isset($_POST['allow_action']) && $_POST['allow_action'] != 'none') {
            if ($_POST['allow_action'] == 'allow_all') {
                if (PHPWS_Settings::get('access', 'allow_all')) {
                    PHPWS_Settings::set('access', 'allow_all', 0);
                } else {
                    PHPWS_Settings::set('access', 'allow_all', 1);
                }
                PHPWS_Settings::save('access');
                return true;
            } elseif (!empty($_POST['allows'])) {
                $db = new PHPWS_DB('access_allow_deny');

                // just in case something goes wrong
                $db->addWhere('allow_or_deny', 1);
                $db->addWhere('id', $_POST['allows']);

                switch ($_POST['allow_action']) {
                    case 'active':
                        $db->addValue('active', 1);
                        return $db->update();
                        break;

                    case 'deactive':
                        $db->addValue('active', 0);
                        return $db->update();
                        break;

                    case 'delete':
                        return $db->delete();
                        break;
                }
            }
        }

        if ($_POST['deny_action'] == 'deny_all') {
            if (PHPWS_Settings::get('access', 'deny_all')) {
                PHPWS_Settings::set('access', 'deny_all', 0);
            } else {
                PHPWS_Settings::set('access', 'deny_all', 1);
            }
            PHPWS_Settings::save('access');
            return true;
        } elseif (!empty($_POST['denys'])) {
            $db = new PHPWS_DB('access_allow_deny');
            // just in case something goes wrong
            $db->addWhere('allow_or_deny', 0);
            $db->addWhere('id', $_POST['denys']);

            switch ($_POST['deny_action']) {
                case 'active':
                    $db->addValue('active', 1);
                    return $db->update();
                    break;

                case 'deactive':
                    $db->addValue('active', 0);
                    return $db->update();
                    break;

                case 'delete':
                    return $db->delete();
                    break;
            }
        }

        return true;
    }

    public static function forward()
    {
        PHPWS_Core::initModClass('access', 'Shortcut.php');
        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('keyword', $GLOBALS['Forward']);
        $db->setLimit(1);
        $scl = $db->getObjects('Access_Shortcut');
        if (@$sc = $scl[0]) {
            $sc->loadGet();
        }
    }

    public static function allowDeny()
    {
        if (!PHPWS_Settings::get('access', 'allow_deny_enabled')) {
            $_SESSION['Access_Allow_Deny'] = true;
            return;
        }

        $address = Access::inflateIp($_SERVER['REMOTE_ADDR']);

        $allow_all = PHPWS_Settings::get('access', 'allow_all');
        $deny_all = PHPWS_Settings::get('access', 'deny_all');

        $db = new PHPWS_DB('access_allow_deny');
        $db->addWhere('active', 1);
        $db->addColumn('allow_or_deny');
        $db->addColumn('ip_address');
        $db->setIndexBy('allow_or_deny');
        $perms = $db->select('col');

        if (isset($perms[1]) && ($allow_all || (!empty($perms[1]) && Access::comparePermissions($perms[1], $address)))) {
            $_SESSION['Access_Allow_Deny'] = true;
        }

        if (isset($perms[0]) && ($deny_all || (!empty($perms[0]) && Access::comparePermissions($perms[0], $address)))) {
            $_SESSION['Access_Allow_Deny'] = false;
            return;
        }

        $_SESSION['Access_Allow_Deny'] = true;
        return;
    }

    public static function comparePermissions($permission_array, $ip)
    {
        if (empty($permission_array)) {
            return false;
        }

        if (is_string($permission_array)) {
            $permission_array = array($permission_array);
        }

        foreach ($permission_array as $ip_compare) {
            $ip_compare = Access::inflateIp($ip_compare);
            $ip_compare = str_replace('.', '\.', $ip_compare);
            if (preg_match("/^$ip_compare/", $ip)) {
                return true;
            }
        }
        return false;
    }

    public static function inflateIp($address)
    {
        $i = explode('.', $address);
        foreach ($i as $sub) {
            $subint = (int) $sub;
            $newip[] = str_pad((string) $subint, 3, '0', STR_PAD_LEFT);
        }

        return implode('.', $newip);
    }

    public static function denied()
    {
        Error::errorPage('403');
    }

    public static function isDenied($ip)
    {
        PHPWS_Core::initModClass('access', 'Allow_Deny.php');
        $ad = new Access_Allow_Deny;
        if (!$ad->setIpAddress($ip)) {
            return false;
        }
        $ad->resetDB();
        $ad->_db->addColumn('id');
        $ad->_db->addWhere('ip_address', $ad->ip_address);
        $ad->_db->addWhere('allow_or_deny', 0);
        $result = $ad->_db->select('one');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        return (bool) $result;
    }

    /**
     * Adds an ip address to the allow or deny database
     */
    public static function addIP($ip, $allow_or_deny = false)
    {
        $allow_or_deny = (int) (bool) $allow_or_deny;

        PHPWS_Core::initModClass('access', 'Allow_Deny.php');
        $ad = new Access_Allow_Deny;
        if (!$ad->setIpAddress($ip)) {
            return false;
        }
        $ad->resetDB();
        $ad->_db->addColumn('id');
        $ad->_db->addWhere('ip_address', $ad->ip_address);
        $ad->_db->addWhere('allow_or_deny', $allow_or_deny);
        $result = $ad->_db->select('one');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if ($result) {
            return true;
        }

        $ad->allow_or_deny = $allow_or_deny;
        $ad->active = 1;
        return $ad->save();
    }

    public static function removeIp($ip, $allow_or_deny = false)
    {
        $allow_or_deny = (int) (bool) $allow_or_deny;

        PHPWS_Core::initModClass('access', 'Allow_Deny.php');
        $ad = new Access_Allow_Deny;
        if (!$ad->setIpAddress($ip)) {
            return false;
        }

        $db = new PHPWS_DB('access_allow_deny');
        $db->addWhere('ip_address', $ad->ip_address);
        $db->addWhere('allow_or_deny', $allow_or_deny);
        return $db->delete();
    }

    public static function htaccess()
    {
        $current_directory = dirname($_SERVER['PHP_SELF']);
        $base_needed = false;

        if (!is_file('.htaccess')) {
            $tpl['CURRENT_HTACCESS'] = dgettext('access', 'Your .htaccess file does not exist or is not readable.');
        } else {
            $htaccess_contents = file('.htaccess');
            $tpl['CURRENT_HTACCESS'] = implode('', $htaccess_contents);
            $base = null;
            if (is_writable('.htaccess')) {
                foreach ($htaccess_contents as $val) {
                    if (preg_match('/^rewritebase/i', trim($val))) {
                        $base = trim(str_ireplace('rewritebase', '', $val));
                    }
                }

                if (!$base) {
                    if ($current_directory == '' || $current_directory == '/') {
                        $tpl['BASE_FOUND'] = dgettext('access', 'RewriteBase is not set or needed.');
                    } else {
                        $base_needed = true;
                        $tpl['BASE_FOUND'] = dgettext('access', 'Your RewriteBase is not set but may be needed.');
                    }
                } elseif ($base == $current_directory) {
                    $tpl['BASE_FOUND'] = dgettext('access', 'Current RewriteBase matches installation directory.');
                } else {
                    $base_needed = true;
                    $tpl['BASE_FOUND'] = dgettext('access', 'Current RewriteBase does not match the installation directory.');
                }
            } else {
                $tpl['BASE_FOUND'] = dgettext('access', 'Your .htaccess file is not writable.');
            }
        }

        if ($base_needed) {
            if (is_writable('.htaccess')) {
                $vars['command'] = 'add_rewritebase';
                $tpl['OPTION'] = PHPWS_Text::secureLink(dgettext('access', 'Add RewriteBase'), 'access', $vars);
            } else {
                $tpl['OPTION'] = dgettext('access', 'Your .htaccess file is not writable. A RewriteBase cannot be added.');
            }
        }

        $content = PHPWS_Template::process($tpl, 'access', 'forms/htaccess.tpl');
        return $content;
    }

    public static function addForward()
    {
        if (Access::removeForward()) {
            $htaccess = Access::getHtaccess();
            if (empty($htaccess)) {
                return false;
            }
            foreach ($htaccess as $key => $val) {
                if (preg_match('/^rewriteengine/i', trim($val))) {
                    $htaccess[$key] = sprintf("RewriteEngine On\nRewriteRule ^js/(.*)$ %s$1 [L,R=301,NC]", PHPWS_SOURCE_HTTP);
                    break;
                }
            }
            if (!empty($htaccess)) {
                file_put_contents('.htaccess', implode('', $htaccess));
            }
            return true;
        }
    }

    public static function removeForward()
    {
        $htaccess = Access::getHtaccess();
        if (empty($htaccess)) {
            return false;
        }

        foreach ($htaccess as $key => $val) {
            if (preg_match('/^rewriterule \^js/i', trim($val))) {
                $htaccess[$key] = "\n";
            }
        }

        if (!empty($htaccess)) {
            file_put_contents('.htaccess', implode('', $htaccess));
        }
        return true;
    }

    public static function getHtaccess()
    {
        if (!is_file('.htaccess') || !is_readable('.htaccess') || !is_writable('.htaccess')) {
            return;
        }

        return file('.htaccess');
    }

    public static function addRewriteBase()
    {
        $htaccess = Access::getHtaccess();
        if (empty($htaccess)) {
            return null;
        }

        $current_directory = dirname($_SERVER['PHP_SELF']);

        $base_found = false;
        foreach ($htaccess as $key => $val) {
            if (preg_match('/^rewritebase/i', trim($val))) {
                $htaccess[$key] = "RewriteBase $current_directory";
                $base_found = true;
                break;
            }
        }
        if (!$base_found) {
            $htaccess[] = "RewriteBase $current_directory";
        }
        if (!empty($htaccess)) {
            file_put_contents('.htaccess', implode('', $htaccess));
        }
    }

    public static function menuFix()
    {
        $db = new PHPWS_DB('access_shortcuts');
        $sc = $db->select();
        if (empty($sc)) {
            return;
        }

        $menu_db = new PHPWS_DB('menu_links');
        foreach ($sc as $shortcut) {
            extract($shortcut);
            $url = str_replace(':', '/', $url);
            $menu_db->addWhere('url', $url);
            $menu_db->addWhere('url', "./$url", '=', 'or');
            $menu_db->addValue('url', './' . $keyword);
            $menu_db->update();
            $menu_db->reset();
        }
    }

    public static function autoForward()
    {
        $current_url = PHPWS_Core::getCurrentUrl();
        if (preg_match('@pagesmith/\d+@', $current_url)) {
            $page_name = str_replace('/', ':', $current_url);
            $db = new PHPWS_DB('access_shortcuts');
            $db->addColumn('keyword');
            $db->addWhere('url', $page_name);
            $db->setLimit(1);
            $keyword = $db->select('one');
            if (!empty($keyword)) {
                PHPWS_Core::reroute($keyword);
                exit();
            }
        }
    }

}
