<?php

namespace phpws2\Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
abstract class ErrorResponse extends \phpws2\Response
{
    protected $request;
    protected $backtrace;
    protected $exception;

    public function __construct(\phpws2\Request $request = null, \Exception $previous = null)
    {
        if (is_null($request)) {
            $request = \Server::getCurrentRequest();
        }

        parent::__construct(null, $this->getHttpResponseCode());

        $this->request = $request;
        $this->code = $this->getHttpResponseCode();
        $this->backtrace = debug_backtrace();
        $this->exception = $previous;
    }

    protected abstract function getHttpResponseCode();

    public function getView()
    {
        if (is_null($this->view)) {
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

    protected function createErrorView(\phpws2\Request $request, \phpws2\Response $response)
    {
        $iter = $request->getAccept()->getIterator();

        foreach ($iter as $type) {
            if ($type->matches('application/json')) {
                return new \View\JsonErrorView($request, $response);
            }
            if ($type->matches('application/xml')) {
                return new \View\XmlErrorView($request, $response);
            }
            if ($type->matches('text/html')) {
                return new \View\HtmlErrorView($request, $response);
            }
        }
    }

}
