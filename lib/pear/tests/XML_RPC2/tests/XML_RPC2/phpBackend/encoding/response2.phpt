--TEST--
Request XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Response.php';
var_dump(XML_RPC2_Backend_Php_Response::encodeFault(2,'A fault string'));
?>
--EXPECT--
string(276) "<?xml version="1.0" encoding="iso-8859-1"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>2</int></value></member><member><name>faultString</name><value><string>A fault string</string></value></member></struct></value></fault></methodResponse>"
