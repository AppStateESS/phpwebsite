--TEST--
Request XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Request.php');
$request = XML_RPC2_Backend_Php_Request::createFromDecode(simplexml_load_string('<?xml version="1.0"?><methodCall><methodName>foo.bar</methodName><params><param><value><string>a string</string></value></param><param><value><int>125</int></value></param><param><value><double>125.2</double></value></param><param><value><dateTime.iso8601>19970716192030</dateTime.iso8601></value></param><param><value><boolean>1</boolean></value></param><param><value><boolean>0</boolean></value></param></params></methodCall>'));
var_dump($request->getMethodName());
$result = ($request->getParameters());
var_dump($result[0]);
var_dump($result[1]);
var_dump($result[2]);
var_dump($result[3]->timestamp);
var_dump($result[3]->xmlrpc_type);
var_dump($result[3]->scalar);
var_dump($result[4]);
var_dump($result[5]);

?>
--EXPECT--
string(7) "foo.bar"
string(8) "a string"
int(125)
float(125.2)
int(869007600)
string(8) "datetime"
string(14) "19970716192030"
bool(true)
bool(false)
