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
require_once PHPWS_SOURCE_DIR . 'core/class/Form2/Select.php';

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

        $input = new Input($type, $name, $value);
        $this->inputs[$name][] = $input;
        return $input;
    }

    public function addSelectInput($name, $value=null, $multiple=false)
    {
        if (preg_match('/[^\w\-\[\]]/', $name)) {
            throw new PEAR_Exception(dgettext('core', 'Improperly formatted input name'));
        }
        $select = new Select($name, $value, $multiple);
        $this->inputs[$name][] = $select;
        return $select;
    }


    public function addHidden($name, $value)
    {
        return $this->addInput('hidden', $name, $value);
    }

    public function addRadio($name, $value)
    {
        if (is_array($value)) {
            foreach ($value as $radio_value) {
                $radio[] = $this->addInput('radio', $name, $radio_value);
            }
            return $radio;
        } else {
            return $this->addInput('radio', $name, $value);
        }
    }

    public function addCheck($name, $value, $label=null)
    {
        return $this->addInput('checkbox', $name, $value);
    }

    public function addSelect($name, array $values, $label=null)
    {
        return $this->addSelectInput($name, $values);
    }

    public function addMultiple($name, array $value, $label=null)
    {
        return $this->addSelectInput($name, $value, true);
    }

    public function addTextField($name, $value=null, $label=null)
    {
        return $this->addInput('text', $name, $value);
    }

    public function addTextArea($name, $value=null, $label=null)
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

    public function addFile($name, $value, $label=null)
    {
        return $this->addInput('file', $name, $value);
    }

    public function addPassword($name, $value, $label=null)
    {
        return $this->addInput('password', $name, $value);
    }

    public function __toString($with_label=true)
    {
        if (empty($this->id)) {
            $this->loadId();
        }
        foreach ($this->inputs as $input_list) {
            foreach ($input_list as $input) {
                $value[] = $input->__toString($with_label);
            }
        }
        $this->setValue('<p>' . implode("</p><p>", $value) . '</p>');
        $result = parent::__toString();
        $this->setValue(null);
        return $result;
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
        foreach ($this->inputs as $name=>$input_list) {
            $name = strtoupper($name);
            if (count($input_list) > 1) {
                $cnt = 1;
                foreach ($input_list as $input) {
                    $name = preg_replace('/\[\]/', '', $name);
                    if ($capitalize_tags) {
                    }
                    $tpl["{$name}_$cnt"] = $input->__toString();
                    $cnt++;
                }
            } else {
                foreach ($input_list as $input) {
                    if ($input->getType() == 'hidden') {
                        $hiddens[] = $input->__toString();
                    } else {
                        $tpl[$name] = $input->__toString();
                    }
                }
            }
        }
        $start = $capitalize_tags ? 'START_FORM' : 'start_form';
        $end = $capitalize_tags ? 'END_FORM' : 'end_form';

        $tpl[$start] = str_replace('</form>', '', parent::__toString());
        if (!empty($hiddens)) {
            $tpl[$start] .= implode("\n", $hiddens);
        }

        $tpl[$end] = '</form>';

        return $tpl;
    }
}
?>