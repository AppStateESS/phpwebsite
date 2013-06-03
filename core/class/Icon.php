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
class Icon extends \Tag\Image {

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
        return parent::__toString();
    }

    public static function show($type, $alt = null)
    {
        $icon = Icon::get($type);
        if ($alt) {
            $icon->setAlt($alt);
        }
        return $icon->__toString();
    }

    public static function getIconSets()
    {
        $sourceDir = PHPWS_SOURCE_DIR . 'plugins/icons/default/';
        $sourceHttp = 'plugins/icons/default/';
        if (is_file($sourceDir . 'icons.php')) {
            $data[] = array('source' => $sourceHttp, 'icons' => Icon::getIconArray($sourceDir));
        }
        return $data;
    }

    public static function getIconArray($sourceDir)
    {
        include $sourceDir . 'icons.php';
        if (empty($icons)) {
            trigger_error(dgettext('core', 'An icons variable was not found.'));
            exit();
        }
        return $icons;
        ;
    }

    /**
     * Loads the current icons setup.
     * @return array Array of icon parameters, used by loadIcon
     */
    public static function getParams()
    {
        static $params = null;
        if (empty($params)) {
            $params = Icon::getIconSets();
        }
        return $params;
    }

    public static function includeStyle()
    {
        static $included = false;
        if ($included) {
            return;
        }
        // Check for theme-based style.css
        $themeDir = Layout::getTheme();
        $filename = 'plugins/icons/default/icon.css';
        Layout::addToStyleList($filename);
        $included = true;
    }

    public function setStyle($style)
    {
        $this->addStyle($style);
    }

    private static function loadIcon($type, &$icon_objects)
    {
        self::includeStyle();
        $params = Icon::getParams();
        // Check both sources for the icon. First hit wins.
        foreach ($params AS $key => $iconSet) {
            if (!empty($iconSet['icons'][$type])) {
                $icon = $iconSet['icons'][$type];
                $src = $iconSet['source'] . $icon['src'];
                break;
            }
        }
        if (empty($icon)) {
            trigger_error(sprintf(dgettext('core', 'Icon type not found: %s'),
                            $type));
            $src = PHPWS_SOURCE_HTTP . 'core/img/not_found.gif';
        }
        $o = new Icon(PHPWS_SOURCE_DIR . $src, PHPWS_SOURCE_HTTP . $src);

        if (isset($icon['class'])) {
            $o->addClass($icon['class']);
        }

        if (isset($icon['x']) && isset($icon['y'])) {
            $o->addStyle(sprintf('background-position : %spx %spx', $icon['x'],
                            $icon['y']));
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
        if (!class_exists('Layout')) {
            trigger_error(dgettext('core', 'Layout class not enabled'),
                    E_USER_ERROR);
        }
        $params = Icon::getParams();

        foreach ($params AS $iconSet) {
            $subcontent = array();
            $icon_list = array_keys($iconSet['icons']);
            foreach ($icon_list as $item) {
                $subcontent[] = Icon::show($item) . ' ' . $item;
            }
            $content[] = '<strong>' . sprintf(dgettext('core',
                                    '<strong>Icons stored at %s'),
                            $iconSet['source'])
                    . '</strong><br />' . implode('<br />', $subcontent);
        }
        if (empty($content)) {
            trigger_error(dgettext('core',
                            'Icon class failed demo. Check settings'),
                    E_USER_ERROR);
        }

        $final = implode('<br />', $content);
        echo Layout::wrap($final);
        exit();
    }

}

?>