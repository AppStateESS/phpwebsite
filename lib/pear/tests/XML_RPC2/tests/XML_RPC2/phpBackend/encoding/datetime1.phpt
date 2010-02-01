--TEST--
Datetime XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Datetime.php';
$time = new XML_RPC2_Backend_Php_Value_Datetime('1997-01-16T19:20:30.45+01:00');
var_dump($time->encode());
?>
--EXPECT--
string(65) "<dateTime.iso8601>1997-01-16T19:20:30.45+01:00</dateTime.iso8601>"
