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

require_once PHPWS_SOURCE_DIR . 'core/class/Tag.php';

class Image extends Tag {
    /**
     * Complete path of image file
     * @var string
     */
    protected $src = null;

    /**
     * Pixel width of image. Format: 123px
     * @var string
     */
    protected $width = null;

    /**
     * Pixel height of image. Format: 123px
     * @var string
     */
    protected $height = null;

    /**
     * Alternate text description of image
     * @var string
     */
    protected $alt = null;

    /**
     * Image title
     * @var string
     */
    protected $title = null;

    public function __construct($src)
    {
        $this->setType('img');
        $this->setSrc($src);
        $this->setOpen(false);
    }

    public function setSrc($src)
    {
        if (preg_match('/[^\w\.\/\s:\-]/', $src)) {
            throw new PEAR_Exception(dgettext('core', 'Improperly formated image src'));
        }
        $this->src = $src;
    }

    public function __toString()
    {
        if (!$this->width || !$this->height) {
            $this->loadDimensions();
        }

        if (empty($this->alt)) {
            $path = explode('/', $this->src);
            $this->alt = end($path);
        }

        if (empty($this->title)) {
            $this->title = $this->alt;
        }

        return parent::__toString();
    }

    public function setWidth($width)
    {
        $this->width = intval($width) . 'px';
    }

    public function setHeight($height)
    {
        $this->height = intval($height) . 'px';
    }


    /**
     * Loads the src image dimensions. Returns false if it failed.
     * @return boolean
     */
    public function loadDimensions()
    {
        if (empty($this->src)) {
            trigger_error(dgettext('core', 'Src variable is empty'));
            return false;
        }

        $dimen = @getimagesize($this->src);
        if (!is_array($dimen)) {
            $this->src = PHPWS_SOURCE_HTTP . 'core/img/not_found.gif';
            trigger_error(sprintf(dgettext('core', '%s not found'), $this->src));
            return false;
        }

        $this->setWidth($dimen[0]);
        $this->setHeight($dimen[1]);
        return true;
    }

    public function setAlt($alt)
    {
        $this->alt = htmlentities($alt);
    }
}

?>