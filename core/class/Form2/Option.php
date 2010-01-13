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

class Option extends Base {
    private $key = null;
    private $optgroup = null;

    public function __construct($value, $name=null)
    {
        static $count = 0;
        parent::__construct('option', 'option', $value);
        if (isset($name)) {
            $this->key = $name;
            $this->setName($name);
        } else {
            $this->key = $count;
            $count++;
        }
    }

    public function setSelected($selected=true)
    {
        if ($selected) {
            $this->selected = 'selected';
        } else {
            unset($this->selected);
        }
    }

    public function setOptgroup($optgroup)
    {
        $this->optgroup = strip_tags($optgroup);
    }

    public function hasOptgroup()
    {
        return !empty($this->optgroup);
    }

    public function getOptgroup()
    {
        return $this->optgroup;
    }
}
?>