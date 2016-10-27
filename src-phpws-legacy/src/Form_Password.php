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
 * Description of Form_Password
 */
class Form_Password extends Form_Element
{

    public $type = 'password';
    public $autocomplete = false;

    public function Form_Password($name, $value = null)
    {
        $this->setName($name);
        $this->setValue($value);
        $this->allowValue = false;
    }

    public function setAutoComplete($bool)
    {
        $this->autocomplete = (bool) $bool;
    }

    public function getAutoComplete()
    {
        if (!$this->autocomplete) {
            return 'autocomplete="off"';
        }
        return null;
    }

    public function get()
    {
        return '<input type="password" '
                . $this->getName(true)
                . $this->getTitle(true)
                . $this->getDisabled()
                . $this->getPlaceholder()
                . $this->getReadOnly()
                . $this->getValue()
                . $this->getWidth(true)
                . $this->getData()
                . $this->getAutoComplete()
                . ' />';
    }

}
