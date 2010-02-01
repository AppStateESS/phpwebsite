--TEST--
XMLRPCext Backend XML-RPC client against phpxmlrpc validator1 (moderateSizeArrayCheck)
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
$tmp = array('foo');
for ($i = 0 ; $i<150 ; $i++) {
	$tmp[] = "bla bla bla";
}
$tmp[] = "bar";
$result = $client->moderateSizeArrayCheck($tmp);
echo($result . "\n");

?>
--EXPECT--
foobar
