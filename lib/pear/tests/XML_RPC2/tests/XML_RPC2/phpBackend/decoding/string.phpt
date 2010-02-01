--TEST--
String XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Value.php');
printf("Native value: %s\n", XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value><string>The quick brown fox jumped over the lazy dog</string></value>'))->getNativeValue());
printf("Native value: %s\n", XML_RPC2_Backend_Php_Value::createFromDecode(simplexml_load_string('<?xml version="1.0"?><value>The quick brown fox jumped over the lazy dog</value>'))->getNativeValue());
?>
--EXPECT--
Native value: The quick brown fox jumped over the lazy dog
Native value: The quick brown fox jumped over the lazy dog
