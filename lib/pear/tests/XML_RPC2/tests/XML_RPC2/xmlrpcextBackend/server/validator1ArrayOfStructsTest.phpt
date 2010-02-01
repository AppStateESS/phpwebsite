--TEST--
XMLRPCext Backend XML-RPC server Validator1 test (arrayOfStructsTest)
--FILE--
<?php
class TestServer {
    /**
     * test function
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param array an array of structs
     * @return int result
     */
    public static function arrayOfStructsTest($array) {
        $result = 0;
        while (list(, $struct) = each($array)) {
        	if (isset($struct['curly'])) {
        		$result = $result + $struct['curly'];
        	}
        }
        return $result;
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
<methodName>validator1.arrayOfStructsTest</methodName>
<params>
 <param>
  <value>
   <array>
    <data>
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
         <int>2</int>
        </value>
       </member>
       <member>
        <name>curly</name>
        <value>
         <int>4</int>
        </value>
       </member>
      </struct>
     </value>
     <value>
      <struct>
       <member>
        <name>moe</name>
        <value>
         <int>0</int>
        </value>
       </member>
       <member>
        <name>larry</name>
        <value>
         <int>1</int>
        </value>
       </member>
       <member>
        <name>curly</name>
        <value>
         <int>12</int>
        </value>
       </member>
      </struct>
     </value>
    </data>
   </array>
  </value>
 </param>
</params>
</methodCall>
EOS
;
$response = $server->getResponse();
var_dump(XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
?>
--EXPECT--
int(24)
