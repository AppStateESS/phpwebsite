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
        switch ($this->type) {
            case 'add':
                $this->addClass('fa fa-plus');
                break;
            case 'approved':
                $this->addClass('fa fa-thumbs-up');
                break;

            case 'cancel':
                $this->addClass('fa fa-ban-circle');
                break;
            case 'clear':
                $this->addClass('fa fa-eraser');
                break;
            case 'clip':
                $this->addClass('fa fa-paperclip');
                break;
            case 'close':
                $this->addClass('fa fa-remove');
                break;

            case 'delete':
                $this->addClass('fa fa-trash-o');
                break;

            case 'email':
                $this->addClass('fa fa-envelope-alt');
                break;

            case 'error':
                $this->addClass('fa fa-exclamation-sign');
                break;

            case 'image':
                $this->addClass('fa fa-picture-o');
                break;

            case 'up':
            case 'down':
                $this->addClass('fa fa-arrow-' . $this->type);
                break;

            case 'active':
                $this->addClass('fa fa-power-off');
                $this->addStyle('color : green');
                break;

            case 'deactive':
            case 'inactive':
                $this->addClass('fa fa-power-off');
                $this->addStyle('color : red');
                break;



            case 'next':
                $this->addClass('fa fa-chevron-right');
                break;

            case 'previous':
                $this->addClass('fa fa-chevron-left');
                break;

            case 'forbidden':
                $this->addClass('fa fa-warning-sign');
                break;

            case 'permission':
                $this->addClass('fa fa-key');
                break;

            default:
                $this->addClass('fa fa-' . $this->type);
        }
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