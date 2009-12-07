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

    public function __construct($type)
    {
        // params contain information on the current icon set
        static $params = null;

        if (empty($params)) {
            $params = $this->pullParams();
        }

        $icon = & $params['icons'][$type];
        if (empty($icon)) {
            return;
        }

        if (isset($icon['map'])) {
            // if using a map, use a blank png
            $src = PHPWS_SOURCE_HTTP . 'images/icons/blank.png';
            $this->setClass($icon['map']);
            $this->setStyle(sprintf('background-position : %s %s', $icon['x'], $icon['y']));
            $this->setWidth($icon['width']);
            $this->setHeight($icon['height']);
        } elseif (isset($icon['src'])) {
            $src = PHPWS_SOURCE_HTTP . 'images/icons/' . $params['source'] . $icon['src'];
        }
        parent::__construct($src);
    }


    public function __toString()
    {
        return parent::__toString();
    }

    private function pullParams()
    {
        $filename = PHPWS_SOURCE_DIR . 'core/conf/icons.php';
        include $filename;

        if (!isset($source)) {
            throw new PEAR_Exception(dgettext('core', 'Icon file missing source directory'));
        }

        if (!empty($maps)) {
            $params['maps'] = $maps;
        }
        $params['source'] = $source;
        $params['icons'] = $icons;
        if (isset($default_icon)) {
            $params['default_icon'] = $default_icon;
        }
        return $params;
    }
}

?>