<?php

class MiniAdmin {
    function add($module, $links)
    {
        if (!is_array($links)) {
            $links = array($links);
        }

        $GLOBALS['MiniAdmin'][$module] = $links;
        return TRUE;
    }

    function get()
    {
        if (!isset($GLOBALS['MiniAdmin'])) {
            return NULL;
        }

        $tpl['MINIADMIN_TITLE'] = _('MiniAdmin');
        foreach ($GLOBALS['MiniAdmin'] as $module => $links) {
            foreach ($links as $link) {
                $tpl['links'][] = array('ADMIN_LINK' => $link);
            }
        }

        $content = PHPWS_Template::process($tpl, 'miniadmin', 'mini_admin.tpl');
        Layout::add($content, 'users', 'mini_admin');
    }
}

?>