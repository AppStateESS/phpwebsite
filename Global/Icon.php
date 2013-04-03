<?php

/**
 * Icon is an Image class extension allowing use of a standard graphic
 * library. Instead of creating an image for each Edit, delete, etc. button,
 * they can use Icon. Repeated use of icons puts less load on the site.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Icon {
    public $style;
    /**
     *
     * @staticvar array $icon_objects
     * @param string $type
     * @return \Icon
     */
    public static function get($type)
    {
        static $icon_objects = null;

        if (!isset($icon_objects[$type])) {
            Icon::loadIcon($type, $icon_objects);
        }
        return clone($icon_objects[$type]);
    }

    /**
     * Returns the icon image string
     * @return string
     */
    public function __toString()
    {
        Icon::includeStyle();
        return parent::__toString();
    }

    /**
     * Default function to display an icon.
     * <code>
     * echo Icon::show('edit', 'Click here to edit');
     * </code>
     * @param string $type Name of the icon needed (e.g. edit, delete)
     * @param string $alt Alternate text added to image tag
     * @return string
     */
    public static function show($type, $alt=null)
    {
        $icon = Icon::get($type);
        if ($alt) {
            $icon->setAlt($alt);
        }
        Icon::includeStyle();
        return $icon->__toString();
    }

    /**
     * Source directory of all icons.
     * @param string $source
     */
    public static function setIconSource($source)
    {
        $GLOBALS['Icon_Source'] = $source;
    }

    /**
     * Return the current source of icon files.
     * @return string
     */
    public static function getIconSource()
    {
        if (!isset($GLOBALS['Icon_Source'])) {
            $GLOBALS['Icon_Source'] = 'default';
        }

        return 'Images/Icons/' . $GLOBALS['Icon_Source'] . '/';
    }

    /**
     * Icon array pulled from icon configuration file.
     * @return array
     */
    public static function getIconArray()
    {
        $filename = Icon::getIconSource() . 'icons.php';
        include $filename;
        if (empty($icons)) {
            throw new \Exception(t('An icons variable was not found.'));
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

    /**
     * Includes the icon configurations style file into the header.
     * @staticvar boolean $included
     */
    public static function includeStyle()
    {
        static $included = false;
        if ($included) {
            return;
        }

        $css = Icon::getIconSource() . 'icon.css';
        $head = Head::singleton();
        $head->includeCSS($css);
        $included = true;
    }

    /**
     * Loads the icon object into the icon queue.
     * @todo this looks incomplete
     * @param string $type
     * @param array $icon_objects
     * @return boolean
     */
    private static function loadIcon($type, &$icon_objects)
    {
        require_once 'Global/Backward/Class/Backward_Image.php';
        $params = Icon::getParams();

        $icon = & $params['icons'][$type];
        if (empty($icon)) {
            trigger_error(sprintf(t('Icon type not found: %s'), $type));
            $src = 'Images/Icon/not_found.gif';
        } else {
            $src = $params['source'] . $icon['src'];
        }
        $o = new Backward_Image($src);

        if (isset($icon['class'])) {
            $o->addClass($icon['class']);
        }

        if (isset($icon['x']) && isset($icon['y'])) {
            $o->addStyle(sprintf('background-position : %spx %spx', $icon['x'], $icon['y']));
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

    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * Echoes a complete list of all icons used in the current configuration.
     */
    public static function demo()
    {
        $icons = Icon::getIconArray();
        $icon_list = array_keys($icons);
        $content = array();

        foreach ($icon_list as $resource) {
            $content[] = Icon::show($resource) . " $resource";
        }

        $final = implode('<br />', $content);
        $head = Head::singleton();
        $head->includeCSS('Images/Icons/' . $GLOBALS['Icon_Source'] . '/icon.css');
        \Body::show($final);
    }
}

?>