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

require_once PHPWS_SOURCE_DIR . 'core/class/Form2/Option.php';

class Select extends Base {
    private $multiple = false;
    private $options = null;
    private $named_options = false;

    protected $selected = null;

    public function __construct($name, array $options, $multiple=false)
    {
        parent::__construct('select');
        $this->setName($name);
        $this->setOptions($options);
    }

    public function setName($name)
    {
        if (!$this->isProper($name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper input name'));
        }
        $this->name = $name;
    }

    public function addOption($value, $name=null)
    {
        if (!$this->isProper($name)) {
            $name = $value;
        }
        $option = new Option($value, $name);
        $this->options[$name] = $option;
        return $option;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key=>$value) {
            if (is_a($value, 'Option')) {
                $this->options[$value->name] = $value;
            } else {
                $this->addOption($value, $key);
            }
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

    public function get($with_label=false)
    {
        if ($with_label && isset($this->id)) {
            return sprintf('<label for="%s">%s</label> %s', $this->id, $this->label, $this->__toString());
        } else {
            return $this->__toString();
        }
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

    public function setSelected($name)
    {
        if (!isset($this->options[$name])) {
            throw new PEAR_Exception(dgettext('core', 'Option index not found in select'));
        }
        $this->options[$name]->setSelected();
    }
}
?>