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
            $title = _('Mod_Rewrite');
            $content = Access::rewrite();
            break;
        }

        $panel->setContent($content);
        $finalPanel = $panel->display();
        
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function rewrite()
    {
        if (!MOD_REWRITE_ENABLED) {
            $content[] = _('You do not have mod rewrite enabled.');
            $content[] = _('Open your config/core/config.php file in a text editor.');
            $content[] = _('Set your "MOD_REWRITE_ENABLED" define equal to TRUE.');
            return implode('<br />', $content);
        }

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

        if (MOD_REWRITE_ENABLED) {
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