<?php

namespace Form\Choice;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Checkbox extends \Form\Choice {
    /**
     * @see Form\Choice::$breaks
     * @var boolean
     */
    protected $breaks = true;

    /**
     * Adds an associative array of elements to the checkbox object
     * @param array $options
     */
    public function addOptions(array $options)
    {
        if (is_array(current($options))) {
            throw new \Exception(t('Checkbox choice does not allow multi-dimensional arrays'));
        }
        if (!is_assoc($options)) {
            $options = array_combine($options, $options);
        }

        foreach ($options as $value => $label) {
            $option = new \Form\Input\Radio($this->getName(), $value, $label);
            if ($this->selection == $value) {
                $options->setSelection(true);
            }
            $this->options[$value] = $option;
        }
    }

    public function getStringArray()
    {
        $text = array();
        foreach ($this->options as $opt) {
            $text[] = $opt->getLabel() . ' ' . $opt->__toString();
        }
        return $text;
    }

    /**
     * Before using the parent setName function, this method adds missing
     * square brackets
     *
     * @param string $name
     */
    public function setName($name)
    {
        if (!preg_match('/\[\w*\]$/', $name)) {
            $name .= '[]';
        }
        parent::setName($name);
    }

    /**
     * Returns a string of all options. For checkboxes and radio buttons, this
     * is the end. For select and multiple inputs, the result will be wrapped
     * with the select tag.
     *
     * @see Choice::$breaks
     * @return string
     */
    public function __toString()
    {
        $text = $this->getStringArray();
        if (!$this->breaks) {
            return implode("\n", $text);
        } else {
            return implode("<br>", $text);
        }
    }

    public function printWithLabel()
    {
        return $this->__toString();
    }

}

?>