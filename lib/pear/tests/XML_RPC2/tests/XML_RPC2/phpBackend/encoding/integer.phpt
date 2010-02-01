--TEST--
Integer XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Value/Integer.php';
$integer = new XML_RPC2_Backend_Php_Value_Integer(53);
var_dump($integer->encode());
?>
--EXPECT--
string(13) "<int>53</int>"
