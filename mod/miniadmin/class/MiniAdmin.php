<?php

/**
 * Simple class to add a module's administrator commands to a box
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::requireConfig('miniadmin');

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
        $modlist = Core\Core::getModuleNames();

        if (!isset($GLOBALS['MiniAdmin'])) {
            return NULL;
        }

        $oTpl = new Core\Template('miniadmin');
        $oTpl->setFile(MINIADMIN_TEMPLATE);

        $tpl['MINIADMIN_TITLE'] = dgettext('miniadmin', 'MiniAdmin');

        foreach ($GLOBALS['MiniAdmin'] as $module => $links) {

            if (!isset($modlist[$module])) {
                continue;
            }

            foreach ($links['links'] as $link) {
                $oTpl->setCurrentBlock('links');
                $oTpl->setData(array('LINE_MODULE' => $modlist[$module],
                                     'ADMIN_LINK' => Core\Text::fixAmpersand($link)));
                $oTpl->parseCurrentBlock();
            }
            $oTpl->setCurrentBlock('module');

            $mod_title = $modlist[$module];

            if (isset($GLOBALS['MiniAdmin'][$module]['title_link'])) {
                $mod_title = sprintf('<a href="%s">%s</a>', $GLOBALS['MiniAdmin'][$module]['title_link'],
                $mod_title);
            }

            $oTpl->setData(array('MODULE' => $mod_title));
            $oTpl->parseCurrentBlock();
        }
        $oTpl->setData($tpl);
        $content = $oTpl->get();

        Layout::set($content, 'miniadmin', 'mini_admin');
    }

    public static function setTitle($module, $link, $add_authkey=false)
    {
        if ($add_authkey) {
            $link = sprintf('%s&amp;authkey=%s', $link, Current_User::getAuthKey());
        }
        $GLOBALS['MiniAdmin'][$module]['title_link'] = $link;
    }
}

?>