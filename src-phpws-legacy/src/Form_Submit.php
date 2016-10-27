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
 * Description of Form_Submit
 */
class Form_Submit extends Form_Element
{

    public $type = 'submit';

    public function get()
    {
        if (isset($this->_form) && $this->_form->required_field) {
            $extra = 'onclick="check(this);"';
            if (!empty($this->extra)) {
                $extra = $this->extra . ' ' . $extra;
            }
            $this->setExtra($extra);
        }
        return '<input type="submit" '
                . $this->getName(true)
                . $this->getValue()
                . $this->getDisabled()
                . $this->getReadOnly()
                . $this->getWidth(true)
                . $this->getData() . ' />';
    }

}
