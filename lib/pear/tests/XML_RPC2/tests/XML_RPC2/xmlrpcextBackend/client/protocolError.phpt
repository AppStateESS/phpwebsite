--TEST--
XMLRPCext Backend XML-RPC client with transport error
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Backend.php';
XML_RPC2_Backend::setBackend('xmlrpcext');
$client = XML_RPC2_Client::create('http://rpc.example.com:1000/', '', null);
try {
    $client->invalidMethod('World');
} catch (XML_RPC2_CurlException $e) {
    var_dump($e->getMessage());
}
?>
--EXPECT--
string(70) "Curl returned non-null errno 6:Couldn't resolve host 'rpc.example.com'"
