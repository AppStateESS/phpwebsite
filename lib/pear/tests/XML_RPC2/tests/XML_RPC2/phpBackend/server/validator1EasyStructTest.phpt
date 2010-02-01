--TEST--
PHP Backend XML-RPC server Validator1 test (easyStructTest)
--FILE--
<?php
class TestServer {
    /**
     * test function
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param array $struct a struct
     * @return int result
     */
    public static function easyStructTest($struct) {
        return ($struct['moe'] + $struct['larry'] + $struct['curly']);
    }
}

set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Server.php';
$options = array(
	'prefix' => 'validator1.',
	'backend' => 'Xmlrpcext'
);

$server = XML_RPC2_Server::create('TestServer', $options);
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<EOS
<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
<methodName>validator1.easyStructTest</methodName>
<params>
 <param>
  <value>
   <struct>
    <member>
     <name>moe</name>
     <value>
      <int>5</int>
     </value>
    </member>
    <member>
     <name>larry</name>
     <value>
      <int>6</int>
     </value>
    </member>
    <member>
     <name>curly</name>
     <value>
      <int>8</int>
     </value>
    </member>
   </struct>
  </value>
 </param>
</params>
</methodCall>
EOS
;
$response = $server->getResponse();
$result = (XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
var_dump($result);

?>
--EXPECT--
int(19)
