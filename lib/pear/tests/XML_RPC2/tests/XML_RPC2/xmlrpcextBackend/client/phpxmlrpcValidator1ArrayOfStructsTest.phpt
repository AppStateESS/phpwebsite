--TEST--
XMLRPCext Backend XML-RPC client against phpxmlrpc validator1 (arrayOfStructsTest)
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
    array(
        'moe' => 5,
        'larry' => 6,
        'curly' => 8
    ),
    array(
        'moe' => 5,
        'larry' => 2,
        'curly' => 4       
    ),
    array(
        'moe' => 0,
        'larry' => 1,
        'curly' => 12      
    )
);
$result = $client->arrayOfStructsTest($arg);
var_dump($result);

?>
--EXPECT--
int(24)
