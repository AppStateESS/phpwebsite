<?php

namespace phpws2\Exception;

/**
 * Exception thrown when a variable is not set.
 * First parameter is the name of the variable
 *
 * @author matt
 */
class WrongType extends \Exception
{

    public function __construct($varname, $var)
    {
        $type = gettype($var);
        if ($type === 'object') {
            $classname = get_class($var);
            $type = "Object $classname";
        }
        $message = "Unexpected variable type '$type' for variable '$varname'";
        parent::__construct($message);
    }

}
