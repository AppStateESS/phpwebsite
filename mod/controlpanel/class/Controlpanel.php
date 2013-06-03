<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Controlpanel {

    private static $toolbar;

    public static function initialize()
    {
        self::$toolbar = new controlpanel\Controlpanel\Toolbar;
    }

    /**
     *
     * @return controlpanel\Controlpanel\Toolbar
     */
    public static function getToolbar()
    {
        return self::$toolbar;
    }

    public static function sendToolbarToLayout()
    {
        $content = self::$toolbar->getSiteOptions();
        \Layout::add($content, 'controlpanel', 'site_options');

        $content = self::$toolbar->getPageOptions();
        \Layout::add($content, 'controlpanel', 'page_options');

        $content = self::$toolbar->getCreateOptions();
        \Layout::add($content, 'controlpanel', 'create_options');

        $content = self::$toolbar->getUserOptions();
        \Layout::add($content, 'controlpanel', 'user_options');
    }

}

Controlpanel::initialize();
?>
