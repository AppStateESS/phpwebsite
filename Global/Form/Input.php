<?php

namespace Form;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Input extends Base {

    /**
     * Value is an empty string because a null value won't allow it to show up
     * in the tag __toString
     * @var string
     */
    protected $value = "";
    /**
     *
     * @var boolean
     */
    protected $open = false;
    /**
     * Title shown for element on mouse-over
     * @var string
     */
    protected $title;

    /**
     * @param string $name
     * @param string $value
     * @param string $label
     */
    public function __construct($name, $value=null, $label=null)
    {
        // Base uses $value as $text on open Tags
        parent::__construct($name, $value, $label);
        $this->setValue($value);
    }

    /**
     * The default value passed for this input when the form is submitted.
     * @param string $value
     */
    public function setValue($value)
    {
        if (isset($value)) {
            $this->value = $value;
        }
        return $this;
    }

    /**
     * Returns the current value of the input object
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the mouse-over title parameter
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = strip_tags($title);
        return $this;
    }

    /**
     * @return string Returns current tag title
     */
    public function getTitle()
    {
        return $this->title;
    }

    }

?>