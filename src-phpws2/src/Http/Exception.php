<?php

namespace phpws2\Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Exception extends \Exception
{
    private $response;

    public final function __construct(\Canopy\Request $request = null, \Exception $previous = null)
    {
        if(is_null($request)) {
            $request = \Canopy\Server::getCurrentRequest();
        }

        $response = $this->createResponse($request, $previous);
        $this->response = $response;

        parent::__construct($response->getPhrase(), $response->getCode(), $previous);
    }

    protected abstract function createResponse(\Canopy\Request $request, \Exception $previous = null);

    public function getResponse()
    {
        return $this->response;
    }
}
