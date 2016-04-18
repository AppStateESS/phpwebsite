<?php

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace phpws;

/**
 * Description of Form_Checkbox
 */
class Form_Checkbox extends Form_Element
{

    public $match = false;
    public $type = 'checkbox';

    public function setMatch($match)
    {
        $this->match = $match;
    }

    public function getMatch()
    {
        if ($this->match === false) {
            return null;
        }

        // If there's a set of matched elements, then $this->match will be an array
        if (is_array($this->match)) {
            // Search the array to see if this element's value should be "matched"
            if (in_array($this->value, $this->match)) {
                return 'checked="checked" ';
            } else {
                return null;
            }
            // Otherwise, $this->match could be a string, so check if its single value
            // matches this elements value
        } else if ((string) $this->match == (string) $this->value) {
            return 'checked="checked" ';
        } else {
            // If nothing matched, just return null
            return null;
        }
    }

    public function get()
    {
        return '<input type="checkbox" ' . $this->getName(true)
                . $this->getTitle(true)
                . $this->getValue()
                . $this->getDisabled()
                . $this->getReadOnly()
                . $this->getMatch()
                . $this->getData()
                . ' />';
    }

}
