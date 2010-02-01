--TEST--
String XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/String.php';
$string = new XML_RPC2_Backend_Php_Value_String('The quick brown fox jumped over the lazy dog');
var_dump($string->encode());
$string = new XML_RPC2_Backend_Php_Value_String('The <quick> brown fox jumped over the lazy dog');
var_dump($string->encode());
?>
--EXPECT--
string(61) "<string>The quick brown fox jumped over the lazy dog</string>"
string(69) "<string>The &lt;quick&gt; brown fox jumped over the lazy dog</string>"
