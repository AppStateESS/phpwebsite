<?php

namespace Form;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Label extends \Tag {

    protected $for;

    protected $required = false;

    public function __construct($text=null, $for = null)
    {
        if (isset($for)) {
            $this->setFor($for);
        }
        parent::__construct('label', $text);
    }

    public function setFor($for)
    {
        $this->for = strip_tags($for);
    }

    public function __toString()
    {
        $label = parent::__toString();

        if ($this->required) {
            $label .= ' <i class="required icon-asterisk"></i>';
        }
        return $label;
    }

    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

}

?>
