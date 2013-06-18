<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NotAcceptableResponse extends ErrorResponse
{
    protected function getHttpResponseCode()
    {
        return 406;
    }
}

?>
