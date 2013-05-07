<?php

namespace Variable;

/**
 * A string extension suitable for using an email address.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @todo Expand functionality
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Email extends String {

    /**
     * @var string The type of variable
     */
    protected $input_type = 'email';

    /**
     * Sets a character limit and makes sure the value is a valid email address.
     * @param string $varname Name of variable
     * @param string $value Email address
     */
    public function __construct($value=null, $varname=null)
    {
        $this->setLimit('80');
        $this->setRegexpMatch('/^[\w.%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i');
        parent::__construct($value, $varname);
    }

}

?>