--TEST--
PHP Backend XML-RPC client against phpxmlrpc validator1 (manyTypesTest)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Value.php';
$options = array(
	'debug' => false,
	'backend' => 'Php',
	'prefix' => 'validator1.'
);
$client = XML_RPC2_Client::create('http://phpxmlrpc.sourceforge.net/server.php', $options);
$tmp = "20060116T19:14:03";
$time = XML_RPC2_Value::createFromNative($tmp, 'datetime');
$base64 = XML_RPC2_Value::createFromNative('foobar', 'base64');
$result = $client->manyTypesTest(1, true, 'foo', 3.14159, $time, $base64);
var_dump($result[0]);
var_dump($result[1]);
var_dump($result[2]);
var_dump($result[3]);
var_dump($result[4]->scalar);
var_dump($result[4]->xmlrpc_type);
var_dump($result[4]->timestamp);
var_dump($result[5]->scalar);
var_dump($result[5]->xmlrpc_type);

?>
--EXPECT--
int(1)
bool(true)
string(3) "foo"
float(3.14159)
string(17) "20060116T19:14:03"
string(8) "datetime"
int(1137435243)
string(6) "foobar"
string(6) "base64"
