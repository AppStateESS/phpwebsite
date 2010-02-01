--TEST--
PHP Backend XML-RPC client against pear.php.net XMLRPC server
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
$options = array(
	'debug' => false,
	'backend' => 'Php',
	'prefix' => 'package.'
);
$client = XML_RPC2_Client::create('http://pear.php.net/xmlrpc.php', $options);
$result = $client->info('XML_RPC2');
if (is_array($result)) {
	print $result['name'] . "\n";
} else {
	die('result is not an array !');
}
?>
--EXPECT--
XML_RPC2
