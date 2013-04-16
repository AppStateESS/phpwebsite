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
     * The inputs label tag
     * @var string
     */
    protected $label;
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
    public function __construct($name, $options=null, $value=null)
    {
        parent::__construct($name);
        $this->setName($name);
        if (!empty($options)) {
            $this->addOptions($options);
        }
        $this->addIgnoreVariables('breaks', 'type', 'label', 'selection', 'options');
        $this->setSelection($value);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        if (!$this->isProper($name)) {
            throw new \Exception(t('Improper input name "%s"', $name));
        }
        $this->name = $name;
    }

    /**
     * @return string Name of current choice input
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the current|default selection for this choice.
     * @param string $selection
     */
    public function setSelection($selection)
    {
        if (empty($selection)) {
            return;
        }
        if (is_array($selection)) {
            foreach ($selection as $s) {
                $this->setSelection($s);
            }
            return;
        }

        if (!empty($selection) && (!isset($this->options[$selection]))) {
            throw new \Exception(t('Selection value "%s" not among current options', $selection));
        }
        $this->options[$selection]->setSelection(true);
        $this->selection = $selection;
    }

    /**
     * Indicates if the text passed will work as an input name.
     * @param string $text
     * @return boolean
     */
    public function isProper($text)
    {
        return preg_match('/^[a-z][\w\-\:\.]*/i', $text);
    }

}

?>