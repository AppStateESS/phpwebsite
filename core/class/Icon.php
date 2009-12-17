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

    public function __construct($src)
    {
        parent::__construct($src);
    }

    public static function get($type)
    {
        static $icon_objects = null;
        if (!isset($icon_objects[$type])) {
            Icon::loadIcon($type, &$icon_objects);
        }
        return $icon_objects[$type];
    }

    public function __toString()
    {
        return parent::__toString();
    }

    public static function show($type, $alt=null)
    {
        $icon = Icon::get($type);
        if ($alt) {
            $icon->setAlt($alt);
        }
        return $icon->__toString();
    }

    private static function loadIcon($type, $icon_objects)
    {
        static $params = null;
        if (empty($params)) {
            /*
             * @todo alternate method for deciding icon set, possible theme override
             */
            $source = 'default';

            $filename = PHPWS_SOURCE_DIR . 'images/icons/' . $source . '/icons.php';
            include $filename;

            if (!isset($source)) {
                throw new PEAR_Exception(dgettext('core', 'Icon file missing source directory'));
            }

            $params['source'] = "images/icons/$source/";

            $params['icons'] = & $icons;
            if (isset($default_icon)) {
                $params['default_icon'] = $default_icon;
            }
            if (class_exists('Layout')) {
                Layout::addToStyleList($params['source'] . 'icon.css');
            }
        }

        $icon = & $params['icons'][$type];
        $src = PHPWS_SOURCE_HTTP . $params['source'] . $icon['src'];
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
    }
}
?>