--TEST--
Datetime XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Value.php');
$result = XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value><dateTime.iso8601>2005</dateTime.iso8601></value>'))->getNativeValue();
var_dump($result->xmlrpc_type);
var_dump($result->scalar);
var_dump($result->timestamp);

?>
--EXPECT--
string(8) "datetime"
string(4) "2005"
int(1104534000)
