<?php

namespace Form\Input;

/**
 * Because hidden isn't displayed , it differs enough from input to extend base
 * instead.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Hidden extends \Form\Base {

    /**
     * Value of the hidden element
     * @var string
     */
    protected $value;
    /**
     * Hidden is a closed tag
     * @var boolean
     */
    protected $open = false;
    /**
     * Hidden isn't seen so the label is irrelevant
     * @var boolean
     */
    protected $has_label = false;
    /**
     * Still has a tag type of input
     * @var string
     */
    protected $tag_type = 'input';

    /**
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        $this->setLabelLocation(0);
        parent::__construct($name, $value);
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}

?>