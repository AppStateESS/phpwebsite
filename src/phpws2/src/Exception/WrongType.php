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

    public function __construct()
    {
        parent::__construct('Unexpected variable type');
    }

}
