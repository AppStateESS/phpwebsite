<?php

namespace Form\Choice;

/**
 * The option tag is used within the select tag and is not used on its own.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Option extends \Tag {

    /**
     * Indicates the optgroup the current option is part of.
     * @var string
     */
    private $optgroup = null;
    /**
     * The value parameter of the option tag
     * @var string
     */
    public $value = null;
    /**
     * If true, adds the "selected" parameter to the option tag.
     * @var boolean
     */
    protected $selected = false;

    /**
     * If the value is not set, the $text parameter is copied to value.
     * @param string $text Text that appears between the option tag
     * @param string $value The value of the option tag.
     * @param string $optgroup Name of group this option is a member of.
     */
    public function __construct($text, $value=null, $optgroup=null)
    {
        parent::__construct('option', $text);
        if (isset($value)) {
            $this->setValue($value);
        } else {
            $this->setValue($text);
        }
        $this->setOptgroup($optgroup);
    }

    /**
     * Indicates this option should have the selected parameter printed.
     * @param boolean $selected
     */
    public function setSelection($selected=true)
    {
        $this->selected = (bool) $selected;
    }

    /**
     * Sets the optgroup you want the option to be part of.
     * @param string $optgroup
     */
    public function setOptgroup($optgroup)
    {
        $str = new \Variable\String($optgroup, 'value');
        $this->optgroup = $str->getStripped();
    }

    /**
     * @return boolean True is the current option has an optgroup
     */
    public function hasOptgroup()
    {
        return!empty($this->optgroup);
    }

    /**
     * Returns the current optgroup of the option
     * @return string
     */
    public function getOptgroup()
    {
        return $this->optgroup;
    }

    /**
     * Sets the option tag's value
     * @param string $value
     */
    public function setValue($value)
    {
        $str = new \Variable\String(is_numeric($value) ? (string)$value:$value, 'value');
        $this->value = $str->getStripped();
    }

    /**
     * Returns the current value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}

?>