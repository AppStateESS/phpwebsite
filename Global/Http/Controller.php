<?php

namespace Http;

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

abstract class Controller implements \Controller
{
    private $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    protected function getModule()
    {
        return $this->module;
    }

    public final function execute(\Request $request)
    {
        $this->onBeforeExecute($request);

        switch($request->getMethod()) {
        case \Request::GET:
            $response = $this->get($request); break;
        case \Request::HEAD:
            $response = $this->head($request); break;
        case \Request::POST:
            $response = $this->post($request); break;
        case \Request::PUT:
            $response = $this->put($request); break;
        case \Request::DELETE:
            $response = $this->delete($request); break;
        case \Request::OPTIONS:
            $response = $this->options($request); break;
        case REQUEST::PATCH:
            $response = $this->patch($request); break;
        default:
            $response = $this->methodNotAllowed(); break;
        }

        $this->onAfterExecute($request, $response);

        return $response;
    }

    public function onBeforeExecute(\Request &$request)
    {
    }

    public function onAfterExecute(\Request $request, \Response &$response)
    {
    }

    protected function methodNotAllowed()
    {
    }

    public function get(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function head(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function post(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function put(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function delete(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function options(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function patch(\Request $request)
    {
        $this->methodNotAllowed();
    }

    public function getView($data, \Request $request = null)
    {
        if(is_null($request)) {
            $request = Server::getCurrentRequest();
        }

        $iter = $request->getAccept()->getIterator();

        foreach($iter as $type) {
            if($type->matches('application/json'))
                return $this->getJsonView($data);
            if($type->matches('application/xml'))
                return $this->getXmlView($data);
            if($type->matches('text/html'))
                return $this->getHtmlView($data);
        }
    }

    public function getJsonView($data)
    {
        return new \JsonView($data);
    }

    public function getXmlView($data)
    {
        // TODO: Find a nice way to just XML encode anything and provide a 
        // default view here.
        throw new NotAcceptableException('application/xml');
    }

    public function getHtmlView($data)
    {
        // TODO: Find a nice way to just HTML encode anything and provide a 
        // default view here.
        throw new NotAcceptableException('text/html');
    }
}

?>
