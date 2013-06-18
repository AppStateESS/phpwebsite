<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class ErrorResponse extends \Response
{
    public function __construct(\Request $request)
    {
        parent::__construct($this->createErrorView($request, $this), $code);
    }

    protected abstract function getHttpResponseCode();

    protected function createErrorView(\Request $request, \Response $response)
    {
        $iter = $request->getAccept()->getIterator();

        foreach($iter as $type) {
            if($type->matches('application/json'))
                return new \Http\JsonErrorView($request, $response);
            if($type->matches('application/xml'))
                return new \Http\XmlErrorView($request, $response);
            if($type->matches('text/html'))
                return new \Http\HtmlErrorView($request, $response);
        }
    }
}

?>
