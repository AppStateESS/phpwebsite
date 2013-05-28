<?php

/**
 * Simple class to add a module's administrator commands to a box
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::requireConfig('miniadmin');

if (!defined('MINIADMIN_TEMPLATE')) {
    define('MINIADMIN_TEMPLATE', 'mini_admin.tpl');
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

        foreach ($GLOBALS['MiniAdmin'] as $module => $links) {
            if (!isset($modlist[$module])) {
                continue;
            }

            $mod_title = (string) $modlist[$module];

            if (isset($GLOBALS['MiniAdmin'][$module]['title_link'])) {
                $module_name = sprintf('<a href="%s">%s</a>',
                        $GLOBALS['MiniAdmin'][$module]['title_link'], $mod_title);
            } else {
                $module_name = $mod_title;
            }

            if (isset($links['links'])) {
                foreach ($links['links'] as $link) {
                    $tpl[$module_name][] = PHPWS_Text::fixAmpersand($link);
                }
            }
            $mod_links['tpl'] = $tpl;
        }

        $tobj = new \Template($mod_links);
        $tobj->setModuleTemplate('miniadmin', 'mini_admin.html');
        Layout::set($tobj->get(), 'miniadmin', 'mini_admin');
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