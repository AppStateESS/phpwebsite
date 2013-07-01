<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class JsonErrorView extends \JsonView
{
    public function __construct(\Request $request, \Http\ErrorResponse $response)
    {
        $json = array();
        $json['url']       = $request->getUrl();
        $json['method']    = $request->getMethod();
        $json['module']    = $request->getModule();
        $json['code']      = $response->getCode();
        $json['phrase']    = $response->getPhrase();
        $json['backtrace'] = $response->getBacktrace();
        $json['exception'] = $response->getException();

        parent::__construct(array('error' => $json));
    }
}

?>
