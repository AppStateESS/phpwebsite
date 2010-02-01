--TEST--
Base64 XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Value.php');
$result = XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value><base64>VGhlIHF1aWNrIGJyb3duIGZveCBqdW1wZWQgb3ZlciB0aGUgbGF6eSBkb2c=</base64></value>'))->getNativeValue();
var_dump($result->xmlrpc_type);
var_dump($result->scalar);
?>
--EXPECT--
string(6) "base64"
string(44) "The quick brown fox jumped over the lazy dog"
