--TEST--
XMLRPCext Backend XML-RPC client against phpxmlrpc validator1 (simpleStructReturnTest)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
$options = array(
	'debug' => false,
	'backend' => 'Xmlrpcext',
	'prefix' => 'validator1.'
);
$client = XML_RPC2_Client::create('http://phpxmlrpc.sourceforge.net/server.php', $options);
$result = $client->simpleStructReturnTest(13);
var_dump($result['times10']);
var_dump($result['times100']);
var_dump($result['times1000']);

?>
--EXPECT--
int(130)
int(1300)
int(13000)
