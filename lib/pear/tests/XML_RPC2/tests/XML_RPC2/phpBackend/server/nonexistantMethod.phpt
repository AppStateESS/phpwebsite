--TEST--
PHP Backend XML-RPC server with non-existant method response
--FILE--
<?php
class EchoServer {
    /**
     * echoecho echoes the message received
     *
     * @param string  Message
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
 <methodName>example</methodName>
 <params><param><value><string>World</string></value></param></params>
</methodCall>
EOS
;
$response = $server->getResponse();
try {
    XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response));
} catch (XML_RPC2_FaultException $e) {
	var_dump($e->getFaultCode());
    var_dump($e->getMessage());
}
?>
--EXPECT--
int(-32601)
string(40) "server error. requested method not found"
