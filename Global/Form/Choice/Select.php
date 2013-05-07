<?php

namespace Form\Choice;

/**
 * The Select input is an extension of the Choice class. It builds a multiple
 * choice, drop-down select box.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Select extends \Form\Choice {

    /**
     * Determines whether the resultant select tag is set to multiple status.
     * @var boolean
     */
    protected $multiple = null;

    /**
     * Returns a select tag wrapped around its option tags.
     * @return string
     */
    public function __toString()
    {
        $options = array();
        $groups = array();
        $suboptions = array();

        foreach ($this->options as $opt) {
            if ($opt->hasOptgroup()) {
                $groups[$opt->getOptgroup()][] = $opt;
            } else {
                $options[] = (string) $opt;
            }
        }

        if (!empty($groups)) {
            foreach ($groups as $optgroup => $group) {
                $suboptions[] = "<optgroup label=\"$optgroup\">";
                $suboptions[] = implode("\n", $group);
                $suboptions[] = '</optgroup>';
            }
            if (!empty($options)) {
                $options = array_merge($suboptions, $options);
            } else {
                $options = & $suboptions;
            }
        }
        $option_string = implode("\n", $options);
        $this->setText($option_string);
        return parent::__toString();
        /**
        $select = new \Form\Input\Select($this->name, $option_string, $this->label);
        $select->print_label = true;
        $select->setMultiple($this->multiple);

        return (string) $select;
         *
         */
    }

    /**
     * Receives an array of options and adds them to the object's option
     * queue.
     * @param array $options Associate array of value=>description data pairs
     * @param string $optgroup Designates the options as part of an optgroup
     */
    public function addOptions(array $options, $optgroup=null)
    {
        if (is_array(current($options))) {
            foreach ($options as $optgroup => $opt) {
                $this->addOptions($opt, $optgroup);
            }
        } else {
            if (!is_assoc($options)) {
                $options = array_combine($options, $options);
            }
            foreach ($options as $value => $text) {
                $this->options[$value] = new \Form\Choice\Option($text, $value, $optgroup);
            }
        }
    }

    /**
     * Changes the multiple value on the select object
     * @param boolean $multiple
     */
    public function setMultiple($multiple)
    {
        $this->multiple = (bool) $multiple;
    }

    /**
     * Sets the label for the select input.
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Indicates whether the output should contain a label.
     * @param boolean $bool
     */
    public function setPrintLabel($bool)
    {
        $this->print_label = (bool) $bool;
    }

    /**
     * @return boolean
     */
    public function getPrintLabel()
    {
        return $this->print_label;
    }

    /**
     * Prefixes the select's label to the select tag.
     * @return string
     */
    public function printWithLabel()
    {
        return $this->label . ' ' . $this->__toString();
    }

}

?>