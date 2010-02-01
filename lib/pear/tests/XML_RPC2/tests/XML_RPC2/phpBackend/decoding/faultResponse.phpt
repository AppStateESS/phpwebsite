--TEST--
Response XML-RPC decoding (Php Backend)
--FILE--
<?php
try {
    set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
    require_once('XML/RPC2/Backend/Php/Response.php');
    var_dump(XML_RPC2_Backend_Php_Response::decode(simplexml_load_string(<<<XMLMARKER
<?xml version="1.0"?>
<methodResponse>
<fault>
<value><struct>
<member><name>faultString</name><value><string>Failed to create homedir with: 0</string></value></member>
<member><name>faultCode</name><value><i4>200</i4></value></member>
</struct></value>
</fault>
</methodResponse>
XMLMARKER
)));
} catch (Exception $e) {
    var_dump($e->getMessage());
}
?>
--EXPECT--
string(32) "Failed to create homedir with: 0"
