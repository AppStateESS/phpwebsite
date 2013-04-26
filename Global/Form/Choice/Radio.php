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
     * @todo may not be needed.
     * @var string
     */
    //protected $option_class = '\Form\Input\Radio';

    /**
     * Radio options have breaks between them.
     * @var boolean
     */
    protected $breaks = true;

    /**
     * @see Form\Choice::addOptions()
     * @param array $options
     */
    public function addOptions(array $options)
    {
        if (empty($this->option_class)) {
            throw new \Exception(t('Option class name is not set'));
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