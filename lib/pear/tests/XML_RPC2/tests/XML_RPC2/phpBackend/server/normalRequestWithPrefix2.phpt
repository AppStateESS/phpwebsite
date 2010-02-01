--TEST--
PHP Backend XML-RPC server with normal response (with prefix in phpdoc)
--FILE--
<?php
class EchoServer {
    /**
     * echoecho echoes the message received
     *
     * @param string  Message
     * @xmlrpc.prefix prefixed.
     * @return string The echo
     */
    public static function echoecho($string) {
        return $string;
    }
}

set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend.php';
require_once 'XML/RPC2/Server.php';
require_once 'XML/RPC2/Backend/Php/Response.php';
XML_RPC2_Backend::setBackend('php');
$server = XML_RPC2_Server::create('EchoServer');
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<EOS
<?xml version="1.0"?>
<methodCall>
 <methodName>prefixed.echoecho</methodName>
 <params><param><value><string>World</string></value></param></params>
</methodCall>
EOS
;
$response = $server->getResponse();
var_dump(XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
?>
--EXPECT--
string(5) "World"