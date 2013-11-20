<?php

namespace Form\Choice;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Radio extends \Form\Choice {

    /**
     * Radio options have breaks between them.
     * @var boolean
     */
    protected $breaks = true;
    protected $label_location = 0;

    /**
     * @see Form\Choice::addOptions()
     * @param array $options
     */
    public function addOptions(array $options)
    {
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
     * Returns a string with all radio buttons including labels.
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