<?php

/**
 * The HttpRequestFactory builds a Request object from data found in an Apache 
 * HTTP Request.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class HttpRequestFactory
{
    public function __construct()
    {
    }

    public function getRequest()
    {
        $url    = Server::getCurrentUrl();
        $method = $_SERVER['REQUEST_METHOD'];
        $vars   = $_REQUEST;
        $data   = file_get_contents('php://input');

        return new Request($url, $method, $vars, $data);
    }
}

?>
