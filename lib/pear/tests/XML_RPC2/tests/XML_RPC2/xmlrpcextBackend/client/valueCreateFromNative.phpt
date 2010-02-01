--TEST--
XMLRPCext backend test setting explicit type for value
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Value.php';
require_once 'XML/RPC2/Backend.php';
XML_RPC2_Backend::setBackend('xmlrpcext');
var_dump(XML_RPC2_Value::createFromNative('Hello World', 'base64'));
?>
--EXPECT--
object(stdClass)#1 (2) {
  ["scalar"]=>
  string(11) "Hello World"
  ["xmlrpc_type"]=>
  string(6) "base64"
}
