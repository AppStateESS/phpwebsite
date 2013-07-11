<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class ErrorResponse extends \Response
{
    protected $request;
    protected $backtrace;
    protected $exception;

    public function __construct(\Request $request = null, \Exception $previous = null)
    {
        if(is_null($request)) {
            $request = \Server::getCurrentRequest();
        }

        $this->request = $request;
        $this->code = $this->getHttpResponseCode();
        $this->backtrace = debug_backtrace();
        $this->exception = $previous;
    }

    protected abstract function getHttpResponseCode();

    public function getView()
    {
        if(is_null($this->view)) {
            $this->view = $this->createErrorView($this->request, $this);
        }

        return $this->view;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getBacktrace()
    {
        return $this->backtrace;
    }

    public function getException()
    {
        return $this->exception;
    }

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
