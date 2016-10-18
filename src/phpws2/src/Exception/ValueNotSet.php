<?php
namespace phpws2\Exception;

/**
 * Exception thrown when a variable is not set.
 * First parameter is the name of the variable
 *
 * @author mcnaneym@appstate.edu
 */
class ValueNotSet extends \Exception
{
    public $varname;
    
    public function __construct($varname=null)
    {
        if (is_string($varname)) {
            $this->varname = $varname;
            $message = 'Value not set: ' . $this->varname;
        } else {
            $message = 'Value not set';
        }
            parent::__construct($message);
            
    }
}
