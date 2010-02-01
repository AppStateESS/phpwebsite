--TEST--
PHP Backend XML-RPC server Validator1 test (moderateSizeArrayCheck)
--FILE--
<?php
class TestServer {
    /**
     * test function
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param array $array an array
     * @return string result
     */
    public static function moderateSizeArrayCheck($array) {
    	return ($array[0] . $array[count($array)-1]);
    }
}

set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Server.php';
$options = array(
	'prefix' => 'validator1.',
	'backend' => 'Php'
);

$server = XML_RPC2_Server::create('TestServer', $options);
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<EOS
<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
<methodName>validator1.moderateSizeArrayCheck</methodName>
<params>
 <param>
  <value>
   <array>
    <data>
     <value>
      <string>foo</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bla bla bla</string>
     </value>
     <value>
      <string>bar</string>
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
$result = (XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
var_dump($result);

?>
--EXPECT--
string(6) "foobar"
