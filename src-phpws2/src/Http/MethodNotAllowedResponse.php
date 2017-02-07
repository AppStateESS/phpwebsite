<?php

namespace phpws2\Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class MethodNotAllowedResponse extends ErrorResponse
{
    protected function getHttpResponseCode()
    {
        return 405;
    }
}
