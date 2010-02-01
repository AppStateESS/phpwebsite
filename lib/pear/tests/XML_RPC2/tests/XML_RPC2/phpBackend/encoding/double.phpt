--TEST--
Double XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Double.php';
$double = new XML_RPC2_Backend_Php_Value_Double(0);
var_dump($double->encode());
$double = new XML_RPC2_Backend_Php_Value_Double(123.79);
var_dump($double->encode());
?>
--EXPECT--
string(18) "<double>0</double>"
string(23) "<double>123.79</double>"
