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

class Select extends Input {
    private $multiple = false;
    private $options = null;

    public function __construct($name, array $options, $multiple=false)
    {
        $this->setTagType('select');
        $this->setOpen(true);
        $this->setName($name);
        $this->setOptions($options);
    }

    public function setOptions($options)
    {
        foreach ($options as $key=>$value) {
            $this->options[$key] = new Input('option', $key, $value);
        }
    }

    public function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new PEAR_Exception(dgettext('core', 'Select option does not exist'));
        }
        return $this->options[$name];
    }

    public function isOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * Alternative to the parent function. The value is set right before the
     * toString is called from Tag. This allows the developer time to
     * alter the options.
     * @return unknown_type
     */
    public function __toString()
    {
        $this->setValue(implode("\n", $this->options));
        return parent::__toString();
    }
}
?>