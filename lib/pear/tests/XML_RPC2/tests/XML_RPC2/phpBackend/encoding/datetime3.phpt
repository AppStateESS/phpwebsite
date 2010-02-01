--TEST--
Datetime XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Datetime.php';
$time = new XML_RPC2_Backend_Php_Value_Datetime('2001');
var_dump($time->encode());
?>
--EXPECT--
string(41) "<dateTime.iso8601>2001</dateTime.iso8601>"
