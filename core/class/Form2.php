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

require_once PHPWS_SOURCE_DIR . 'core/class/Form2/Input.php';

class Form2 extends Tag {
    /**
     * Array of input objects
     * @var array
     */
    private $inputs = array();

    protected $action = 'test.php';

    protected $method = 'post';

    public function __construct()
    {
        static $default_id = 1;
        $this->setTagType('form');
        $this->setId('pws-form-' . $default_id);
        $default_id++;
    }

    public function addInput($type, $name, $value=null)
    {
        if (preg_match('/[^\w\-\[\]]/', $name)) {
            throw new PEAR_Exception(dgettext('core', 'Improperly formatted input name'));
        }
        $input = new Input($type);
        $input->setName($name);
        if (isset($value)) {
            $input->setValue($value);
        }
        $this->inputs[$name][] = $input;
        return $input;
    }


    public function addHidden($name, $value)
    {
        return $this->addInput('hidden', $name, $value);
    }

    public function addRadio($name, $value)
    {
        return $this->addInput('radio', $name, $value);
    }

    public function addCheck($name, $value)
    {
        return $this->addInput('checkbox', $name, $value);
    }

    public function addSelect($name, $value)
    {
        return $this->addInput('select', $name, $value);
    }

    public function addMultiple($name, $value)
    {
        return $this->addInput('multiple', $name, $value);
    }

    public function addTextField($name, $value=null)
    {
        return $this->addInput('text', $name, $value);
    }

    public function addTextArea($name, $value=null)
    {
        return $this->addInput('textarea', $name, $value);
    }

    public function addSubmit($name, $value)
    {
        return $this->addInput('submit', $name, $value);
    }

    public function addButton($name, $value)
    {
        return $this->addInput('button', $name, $value);
    }

    public function addFile($name, $value)
    {
        return $this->addInput('file', $name, $value);
    }

    public function addPassword($name, $value)
    {
        return $this->addInput('password', $name, $value);
    }

    /**
     * Loads the value variable in the Tag class. Variable result will
     * contain a string derived from all known inputs.
     * @return unknown_type
     */
    private function loadValue()
    {
        foreach ($this->inputs as $input_list) {
            foreach ($input_list as $input) {
                $value[] = $input->get(true);
            }
        }
        $this->setValue('<p>' . implode("</p><p>", $value) . '</p>');
    }

    public function __toString()
    {
        $this->loadValue();
        return parent::__toString();
    }

    /**
     * Puts the inputs in the form2 object into an associative array for use in
     * a template.
     * Unlike PHPWS_Form, default tags are lowercase. Set capitalize_tags to true to
     * change it
     * @param boolean $capitalize_tags  Force the capitalization of tags.
     * @return array
     */
    public function getTemplate($capitalize_tags=false)
    {

    }
}
?>