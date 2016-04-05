<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Exception extends \Exception
{
    private $response;

    public final function __construct(\Request $request = null, Exception $previous = null)
    {
        if(is_null($request)) {
            $request = \Server::getCurrentRequest();
        }

        $response = $this->createResponse($request, $previous);
        $this->response = $response;

        parent::__construct($response->getPhrase(), $response->getCode(), $previous);
    }

    protected abstract function createResponse(\Request $request, \Exception $previous = null);

    public function getResponse()
    {
        return $this->response;
    }
}

?>
