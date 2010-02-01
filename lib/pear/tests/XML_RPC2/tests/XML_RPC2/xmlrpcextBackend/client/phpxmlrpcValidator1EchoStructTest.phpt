--TEST--
XMLRPCext Backend XML-RPC client against phpxmlrpc validator1 (echoStructTest)
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
$arg = array(
    'moe' => 5,
    'larry' => 6,
    'curly' => 8
);
$result = $client->echoStructTest($arg);
var_dump($result);

?>
--EXPECT--
array(3) {
  ["moe"]=>
  int(5)
  ["larry"]=>
  int(6)
  ["curly"]=>
  int(8)
}
