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

    /**
     * @var string
     */
    private $module;

    public static function get($type, $module = 'core')
    {
        static $icon_objects = null;
        if (!isset($icon_objects[$module][$type])) {
            Icon::loadIcon($module, $type, $icon_objects);
        }
        return $icon_objects[$module][$type];
    }

    public function __toString()
    {
        Icon::includeStyle($this->module);
        return parent::__toString();
    }

    public static function show($type, $alt = null, $module = 'core')
    {
        $icon = Icon::get($type, $module);
        if ($alt) {
            $icon->setAlt($alt);
        }
        return $icon->__toString();
    }

    public static function getIconSets($module = 'core')
    {
        $data = array();
        // Check for theme-based icons
        if (class_exists('Layout')) {
            $sourceHttp = Layout::getThemeDir() . "templates/$module/icons/";
            $sourceDir = PHPWS_SOURCE_DIR . $sourceHttp;
            if (is_file($sourceDir)) {
                $data[] = array('source' => $sourceHttp, 'icons' => Icon::getIconArray($sourceDir));
            }
        }
        // Get distro icon address
        if ($module == 'core') {
            $sourceDir = PHPWS_SOURCE_DIR . 'images/icons/default/';
            $sourceHttp = 'images/icons/default/';
        } else {
            $sourceDir = PHPWS_SOURCE_DIR . "mod/$module/templates/icons/";
            $sourceHttp = "mod/$module/templates/icons/";
        }
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
    public static function getParams($module = 'core')
    {
        static $params = null;
        if (!empty($params[$module])) {
            return $params;
        }
        $params[$module] = Icon::getIconSets($module);
        return $params;
    }

    public static function includeStyle($module)
    {
        static $included = false;
        if (!empty($included[$module])) {
            return;
        }
        // Mark this module's css as included
        $included[$module] = true;
        // Check for theme-based style.css
        if (class_exists('Layout')) {
            $themeDir = Layout::getTheme();
            $filename = "themes/$themeDir/templates/$module/icons/icon.css";
            if (is_file($filename)) {
                Layout::addToStyleList($filename);
                $included[$module] = true;
                return;
            }
        }
        // Get distro style.css
        if ($module == 'core') {
            $filename = 'images/icons/default/icon.css';
        } else {
            $filename = "mod/$module/templates/icons/icon.css";
        }
        if (is_file(PHPWS_SOURCE_DIR . $filename)) {
            Layout::addToStyleList($filename);
        }
    }

    public function setStyle($style)
    {
        $this->addStyle($style);
    }

    private static function loadIcon($module, $type, &$icon_objects)
    {
        $params = Icon::getParams($module);
        // Check both sources for the icon. First hit wins.
        foreach ($params[$module] AS $key => $iconSet) {
            if (!empty($iconSet['icons'][$type])) {
                $icon = $iconSet['icons'][$type];
                $src = $iconSet['source'] . $icon['src'];
                break;
            }
        }
        if (empty($icon)) {
            trigger_error(sprintf(dgettext('core',
                                    'Icon type not found: %1$s::%2$s'), $module,
                            $type));
            $src = PHPWS_SOURCE_HTTP . 'core/img/not_found.gif';
        }
        $o = new Icon(PHPWS_SOURCE_DIR . $src, PHPWS_SOURCE_HTTP . $src);
        $o->module = $module;

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
        $icon_objects[$module][$type] = $o;

        return true;
    }

    public static function demo($module = 'core')
    {
        if (!class_exists('Layout')) {
            trigger_error(dgettext('core', 'Layout class not enabled'),
                    E_USER_ERROR);
        }
        $params = Icon::getParams($module);

        foreach ($params[$module] AS $iconSet) {
            $subcontent = array();
            $icon_list = array_keys($iconSet['icons']);
            foreach ($icon_list as $item) {
                $subcontent[] = Icon::show($item, null, $module) . ' ' . $item;
            }
            $content[] = '<strong>' . sprintf(dgettext('core',
                                    '<strong>Module %1$s icons stored at %2$s')
                            , $module, $iconSet['source'])
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