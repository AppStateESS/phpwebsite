<?php

namespace Form;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Choice extends Base {

    /**
     * @var string
     */
    protected $tag_type = 'select';

    /**
     * Type of choice - option, radio, or checkbox
     * @var unknown_type
     * @todo Not sure if this is used.
     */
    //protected $option_class;
    /**
     * Options from which the user chooses. Object type depends on
     * class that inherits
     * @var array
     */
    protected $options;

    /**
     * The default choice shown among the available selections.
     * @var string
     */
    protected $selection;

    /**
     * If true, use breaks instead of newlines on options print out. Breaks are
     * used for radio and checkboxes and not used for selects and multiples.
     * @var boolean
     */
    protected $breaks = false;

    /**
     * Loads option stack with an array of options
     * @param array $options
     */
    abstract public function addOptions(array $options);

    //abstract public function printWithLabel();

    /**
     * Options are expected to be in an associate array.
     *
     * @param string $name
     * @param array $options
     * @param unknown_type $value
     */
    public function __construct($name, $options = null, $selection = null)
    {
        parent::__construct($name);
        $this->setName($name);
        if (!empty($options)) {
            $this->addOptions($options);
        }
        $this->addIgnoreVariables('breaks', 'type', 'label', 'selection',
                'options');
        $this->setSelection($selection);
    }

    /**
     * Sets the current|default selection for this choice.
     * @param string $selection
     */
    public function setSelection($selection)
    {
        if (empty($selection)) {
            return $this;
        }
        if (is_array($selection)) {
            foreach ($selection as $s) {
                $this->setSelection($s);
            }
            return $this;
        }

        if (!empty($selection) && (!isset($this->options[$selection]))) {
            throw new \Exception(t('Selection value "%s" not among current options',
                    $selection));
        }
        $this->options[$selection]->setSelection(true);
        $this->selection = $selection;
        return $this;
    }

}
