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
     * The class constructed to for the options for this class
     * @var string
     */
    protected $option_class = '\Form\Input\Checkbox';
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
        if (empty($this->option_class)) {
            throw new \Exception(t('Option class name is not set'));
        }

        if (is_array(current($options))) {
            throw new \Exception(t('Checkbox choice does not allow multi-dimensional arrays'));
        }
        if (!is_assoc($options)) {
            $options = array_combine($options, $options);
        }

        foreach ($options as $key => $value) {
            $this->options[$key] = new \Form\Input\Checkbox($this->getName(), $key, $value);
        }
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
        $text = array();
        foreach ($this->options as $opt) {
            $text[] = (string) $opt;
        }
        if (!$this->breaks) {
            return implode("\n", $text);
        } else {
            return implode("<br>", $text);
        }
    }
}

?>