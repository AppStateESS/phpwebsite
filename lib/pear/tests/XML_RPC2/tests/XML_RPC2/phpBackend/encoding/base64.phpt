--TEST--
Base64 XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Base64.php';
$string = new XML_RPC2_Backend_Php_Value_Base64('The quick brown fox jumped over the lazy dog');
var_dump($string->encode());
$string = new XML_RPC2_Backend_Php_Value_Base64('The <quick> brown fox jumped over the lazy dog');
var_dump($string->encode());
?>
--EXPECT--
string(77) "<base64>VGhlIHF1aWNrIGJyb3duIGZveCBqdW1wZWQgb3ZlciB0aGUgbGF6eSBkb2c=</base64>"
string(81) "<base64>VGhlIDxxdWljaz4gYnJvd24gZm94IGp1bXBlZCBvdmVyIHRoZSBsYXp5IGRvZw==</base64>"
