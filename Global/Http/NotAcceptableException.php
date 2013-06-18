<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NotAcceptableException extends Exception
{
    protected function createResponse(\Request $request)
    {
        return new NotAcceptableResponse($request);
    }
}

?>
