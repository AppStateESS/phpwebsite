--TEST--
Request XML-RPC encoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Backend/Php/Response.php';
var_dump(XML_RPC2_Backend_Php_Response::encode(array(1, true, 'a string')));
?>
--EXPECT--
string(253) "<?xml version="1.0" encoding="iso-8859-1"?><methodResponse><params><param><value><array><data><value><int>1</int></value><value><boolean>1</boolean></value><value><string>a string</string></value></data></array></value></param></params></methodResponse>"
