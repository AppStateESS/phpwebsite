--TEST--
Array XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Array.php';
$array = new XML_RPC2_Backend_Php_Value_Array(array(1, true, 'a string'));
var_dump($array->encode());
?>
--EXPECT--
string(130) "<array><data><value><int>1</int></value><value><boolean>1</boolean></value><value><string>a string</string></value></data></array>"
