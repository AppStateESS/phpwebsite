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
class Icon extends \Tag {

    protected $open = true;
    private $type;

    public function __construct($type = null)
    {
        parent::__construct('i');
        if ($type) {
            $this->type = $type;
        }
    }

    public function setType($type)
    {
        $this->type = preg_replace('/[\s_]/', '-', $type);
    }

    public function setStyle($style)
    {
        $this->addStyle($style);
    }

    public function setAlt($alt)
    {
        $this->setTitle($alt);
    }

    public static function get($type)
    {
        return new self($type);
    }

    public function __toString()
    {
        $this->addIconClass();
        return parent::__toString();
    }

    private function addIconClass()
    {
        $this->addClass('icon-' . $this->type);
    }

    public static function show($type, $title = null)
    {
        $icon = new self($type);
        if ($title) {
            $icon->setTitle($title);
        }
        return $icon->__toString();
    }

    public static function demo()
    {

    }

}

?>