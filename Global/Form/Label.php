<?php

namespace Form;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Label extends \Tag {

    protected $for;

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

}

?>
