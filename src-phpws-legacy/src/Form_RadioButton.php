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
 * Description of Form_RadioButton
 */
class Form_RadioButton extends Form_Element
{

    public $type = 'radio';
    public $match = false;

    public function setMatch($match)
    {
        $this->match = $match;
    }

    public function getMatch()
    {
        if ((string) $this->match == (string) $this->value) {
            return 'checked="checked" ';
        } else {
            return null;
        }
    }

    public function get()
    {
        return '<input type="radio" ' . $this->getName(true)
                . $this->getTitle(true)
                . $this->getValue()
                . $this->getDisabled()
                . $this->getReadOnly()
                . $this->getMatch()
                . $this->getData()
                . ' />';
    }

}
