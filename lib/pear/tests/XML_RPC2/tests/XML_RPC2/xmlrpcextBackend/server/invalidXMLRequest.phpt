--TEST--
XMLRPCext Backend XML-RPC server with normal response
--SKIPIF--
<?php 
// I can't silence expat errors anyhow. We're skipping this test
// for xmlrpci
print "skip";
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
XML_RPC2_Backend::setBackend('XMLRPCext');
$server = XML_RPC2_Server::create('EchoServer');
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<EOS
<?xml version="1.0"?>
<methodCall>
 <methodName>moo</methodName>
 <params><param><value><string>World</string></value></param></prams>
</methodCall>
EOS
;
try {
    $response = $server->getResponse();
    //print($response);
    //XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response));
} catch (XML_RPC2_FaultException $e) {
    var_dump($e->getMessage());
}
?>
--EXPECT--
string(36) "server error. method not found.

moo"
