<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class InternalServerErrorException extends Exception
{
    protected function createResponse(\Request $request, \Exception $previous = null)
    {
        return new InternalServerErrorResponse($request, $previous);
    }
}

?>
