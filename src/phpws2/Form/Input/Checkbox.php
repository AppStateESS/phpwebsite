<?php

namespace Form\Input;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Checkbox extends \Form\Input {

    /**
     * Default label location is after the checkbox input
     * @var integer
     */
    protected $label_location = 1;
    /**
     * True status adds "checked" parameter to tag
     * @var boolean
     */
    protected $checked;
    /**
     * Checkbox is a closed input tag
     * @var boolean
     */
    protected $open = false;

    /**
     * If true, set show the "checked" status of this input.
     * @param boolean $selection
     */
    public function setSelection($selection)
    {
        $this->checked = (bool) $selection;
    }

    /**
     * Alternate for setSelection
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->setSelection($checked);
    }

}

?>