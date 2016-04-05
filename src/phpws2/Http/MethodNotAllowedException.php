<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class MethodNotAllowedException extends Exception
{
    protected function createResponse(\Request $request, \Exception $previous = null)
    {
        return new MethodNotAllowedResponse($request, $previous);
    }
}

?>
