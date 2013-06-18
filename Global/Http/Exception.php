<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Exception extends \Exception
{
    private $response;

    public final function __construct(\Request $request, Exception $previous = null)
    {
        $response = $this->createResponse($request);
        $this->response = $response;

        parent::__construct($response->getPhrase(), $response->getCode(), $previous);
    }

    protected abstract function createResponse(\Request $request);

    public function getResponse()
    {
        return $this->response;
    }
}

?>
