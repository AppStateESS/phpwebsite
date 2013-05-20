<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Layout {

    public static $Head;
    public static $Content;
    public static $Footer;
    public static $Theme;

    public static function init()
    {
        self::$Head = new \layout\Head;
        self::$Content = new \layout\Content;
        self::$Footer = new \layout\Footer;
        self::$Theme = new \layout\Theme;
    }

}

?>
