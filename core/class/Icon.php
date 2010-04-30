<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

require_once PHPWS_SOURCE_DIR . 'core/class/Image.php';

class Icon extends Image {

    public static function get($type)
    {
        static $icon_objects = null;

        if (!isset($icon_objects[$type])) {
            Icon::loadIcon($type, $icon_objects);
        }
        return $icon_objects[$type];
    }

    public function __toString()
    {
        Icon::includeStyle();
        return parent::__toString();
    }

    public static function show($type, $alt=null)
    {
        $icon = Icon::get($type);
        if ($alt) {
            $icon->setAlt($alt);
        }
        Icon::includeStyle();
        return $icon->__toString();
    }

    public static function setIconSource($source)
    {
        $GLOBALS['Icon_Source'] = $source;
    }

    public static function getIconSource()
    {
        if (!isset($GLOBALS['Icon_Source'])) {
            $GLOBALS['Icon_Source'] = 'default';
        }

        return 'images/icons/' . $GLOBALS['Icon_Source'] . '/';
    }

    public static function getIconArray()
    {
        $filename = Icon::getIconSource() . 'icons.php';
        include PHPWS_SOURCE_DIR . $filename;
        if (empty($icons)) {
            trigger_error(dgettext('core', 'An icons variable was not found.'));
            exit();
        }
        return $icons;
    }

    /**
     * Loads the current icons setup.
     * @return array Array of icon parameters, used by loadIcon
     */
    public static function getParams()
    {
        static $params = null;
        if (!empty($params)) {
            return $params;
        }

        $icons = Icon::getIconArray();

        $params['source'] = Icon::getIconSource();

        $params['icons'] = & $icons;
        if (isset($default_icon)) {
            $params['default_icon'] = $default_icon;
        }

        return $params;
    }

    public static function includeStyle()
    {
        static $included = false;
        if ($included) {
            return;
        }

        $css = Icon::getIconSource() . 'icon.css';
        Layout::addToStyleList($css);
        $included = true;
    }


    private static function loadIcon($type, &$icon_objects)
    {
        $params = Icon::getParams();

        $icon = & $params['icons'][$type];
        if (empty($icon)) {
            trigger_error(sprintf(dgettext('core', 'Icon type not found: %s'), $type));
            $src = PHPWS_SOURCE_HTTP . 'core/img/not_found.gif';
        } else {
            $src = PHPWS_SOURCE_HTTP . $params['source'] . $icon['src'];
        }
        $o = new Icon($src);

        if (isset($icon['class'])) {
            $o->setClass($icon['class']);
        }

        if (isset($icon['x']) && isset($icon['y'])) {
            $o->setStyle(sprintf('background-position : %spx %spx', $icon['x'], $icon['y']));
        }

        if (isset($icon['width'])) {
            $o->setWidth($icon['width']);
        }

        if (isset($icon['height'])) {
            $o->setHeight($icon['height']);
        }

        if (isset($icon['label'])) {
            $o->setAlt($icon['label']);
        }
        $icon_objects[$type] = $o;

        return true;
    }

    public static function demo()
    {
        $icons = Icon::getIconArray();
        $icon_list = array_keys($icons);

        foreach ($icon_list as $item) {
            $content[] = Icon::show($item) . " $item";
        }

        $final =  implode('<br />', $content);
        echo Layout::wrap($final);
        exit();
    }
}
?>