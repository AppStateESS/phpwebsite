--TEST--
Boolean XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Boolean.php';
$bool = new XML_RPC2_Backend_Php_Value_Boolean(true);
var_dump($bool->encode());
$bool = new XML_RPC2_Backend_Php_Value_Boolean(false);
var_dump($bool->encode());
?>
--EXPECT--
string(20) "<boolean>1</boolean>"
string(20) "<boolean>0</boolean>"
