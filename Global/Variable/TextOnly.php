<?php

namespace Variable;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class TextOnly extends \Variable\String {

    protected $regexp_match = '/[^<>]/i';

    public function __construct($value=null, $varname=null)
    {
        // No tags allowed
        $this->addAllowedTags(null);
        parent::__construct($value, $varname);
    }

}

?>
