--TEST--
XMLRPCext Backend XML-RPC server Validator1 test (countTheEntities)
--FILE--
<?php
class TestServer {
    /**
     * test function
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param string a string
     * @return array result
     */
    public static function countTheEntities($string) {
        $ctLeftAngleBrackets = substr_count($string, '<');
        $ctRightAngleBrackets = substr_count($string, '>');
        $ctAmpersands = substr_count($string, '&');
        $ctApostrophes = substr_count($string, "'");
        $ctQuotes = substr_count($string, '"');       
        return array(
        	'ctLeftAngleBrackets' => $ctLeftAngleBrackets,
        	'ctRightAngleBrackets' => $ctRightAngleBrackets,
        	'ctAmpersands' => $ctAmpersands,
        	'ctApostrophes' => $ctApostrophes,
        	'ctQuotes' => $ctQuotes
        );
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
<methodName>validator1.countTheEntities</methodName>
<params>
 <param>
  <value>
   <string>foo &#60;&#60;&#60; bar '&#62; &#38;&#38; '' #fo&#62;o &#34; bar</string>
  </value>
 </param>
</params>
</methodCall>
EOS
;
$response = $server->getResponse();
$result = (XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
var_dump($result['ctLeftAngleBrackets']);
var_dump($result['ctRightAngleBrackets']);
var_dump($result['ctAmpersands']);
var_dump($result['ctApostrophes']);
var_dump($result['ctQuotes']);

?>
--EXPECT--
int(3)
int(2)
int(2)
int(3)
int(1)
