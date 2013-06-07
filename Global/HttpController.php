<?php

/**
 * HttpController Abstract Class.  We highly recommend extending this for each 
 * controller that can be returned by your Module instance.  It makes RESTful 
 * APIs easy and fun!
 *
 * All methods are implemented by default to return 405 Method Not Allowed.  
 * Override only the methods that you require within your software.
 *
 * Additionally, onBeforeExecute() and onAfterExecute() can optionally be 
 * overridden within your module to do things at execute time regardless of HTTP 
 * request method.
 *
 * @package Global
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class HttpController implements Controller
{
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public final function execute(Request $request)
    {
        $this->onBeforeExecute($request);

        switch($request->getMethod()) {
        case Request::GET:
            return $this->get($request);
        case Request::HEAD:
            return $this->head($request);
        case Request::POST:
            return $this->post($request);
        case Request::PUT:
            return $this->put($request);
        case Request::DELETE:
            return $this->delete($request);
        case Request::OPTIONS:
            return $this->options($request);
        case REQUEST::PATCH:
            return $this->patch($request);
        default:
            $this->methodNotAllowed();
        }

        $this->onAfterExecute($request);
    }

    public function onBeforeExecute(Request $request)
    {
    }

    public function onAfterExecute(Request $request)
    {
    }

    protected function methodNotAllowed()
    {
        header('HTTP/1.1 405 Method Not Allowed');
        exit();
    }

    public function get(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function head(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function post(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function put(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function delete(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function options(Request $request)
    {
        $this->methodNotAllowed();
    }

    public function patch(Request $request)
    {
        $this->methodNotAllowed();
    }
}

?>
