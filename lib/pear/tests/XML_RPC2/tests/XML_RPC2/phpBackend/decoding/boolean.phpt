--TEST--
Boolean XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Value.php');
printf("Native value: %s\n", XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value><boolean>0</boolean></value>'))->getNativeValue() ? 'true' : 'false');
printf("Native value: %s\n", XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value><boolean>1</boolean></value>'))->getNativeValue() ? 'true' : 'false');
?>
--EXPECT--
Native value: false
Native value: true
