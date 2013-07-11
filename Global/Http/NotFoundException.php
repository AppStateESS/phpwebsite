<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NotFoundException extends Exception
{
    protected function createResponse(\Request $request, \Exception $previous = null)
    {
        return new NotFoundResponse($request, $previous);
    }
}

?>
