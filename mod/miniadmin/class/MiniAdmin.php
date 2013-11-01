<?php

/**
 * Simple class to add a module's administrator commands to a box
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::requireConfig('miniadmin');

if (!defined('MINIADMIN_TEMPLATE')) {
    define('MINIADMIN_TEMPLATE', 'mini_admin.html');
}

class MiniAdmin {

    public static function add($module, $links)
    {
        if (is_array($links)) {
            foreach ($links as $link) {
                MiniAdmin::add($module, $link);
            }
            return true;
        }

        $GLOBALS['MiniAdmin'][$module]['links'][] = $links;
        return true;
    }

    public static function get()
    {
        $modlist = PHPWS_Core::getModuleNames();

        if (!isset($GLOBALS['MiniAdmin'])) {
            return NULL;
        }

        $tpl['MINIADMIN_TITLE'] = dgettext('miniadmin', 'MiniAdmin');

        foreach ($GLOBALS['MiniAdmin'] as $module => $links) {
            $mod_title = $modlist[$module];
            if (isset($links['title_link'])) {
                $mod_title = sprintf('<a href="%s">%s</a>',
                        $links['title_link'], $mod_title);
            }
            $module_links[$mod_title] = $links;
        }
        $tpl['module_links'] = $module_links;
        $template = new \Template($tpl);
        $template->setModuleTemplate('miniadmin', MINIADMIN_TEMPLATE);
        $content = $template->get();
        Layout::set($content, 'miniadmin', 'mini_admin');
    }

    public static function setTitle($module, $link, $add_authkey = false)
    {
        if ($add_authkey) {
            $link = sprintf('%s&amp;authkey=%s', $link,
                    Current_User::getAuthKey());
        }
        $GLOBALS['MiniAdmin'][$module]['title_link'] = $link;
    }

}

?>
