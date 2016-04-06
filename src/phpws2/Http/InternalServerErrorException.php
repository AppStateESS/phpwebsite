<?php

namespace phpws2\Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class InternalServerErrorException extends Exception
{
    protected function createResponse(\phpws2\Request $request, \Exception $previous = null)
    {
        return new InternalServerErrorResponse($request, $previous);
    }
}

?>
