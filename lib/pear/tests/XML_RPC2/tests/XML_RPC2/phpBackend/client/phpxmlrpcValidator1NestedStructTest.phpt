--TEST--
PHP Backend XML-RPC client against phpxmlrpc validator1 (nestedStructTest)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
$options = array(
	'debug' => false,
	'backend' => 'Php',
	'prefix' => 'validator1.'
);
$client = XML_RPC2_Client::create('http://phpxmlrpc.sourceforge.net/server.php', $options);

$year1999 = array(
  '04' => array()
);
$year2001 = $year1999;
$year2000 = $year1999;
$year2000['04']['01'] = array(
	'moe' => 12,
	'larry' => 14,
	'curly' => 9
);

$index1999 = '1999 ';
$index2000 = '2000 ';
$index2001 = '2001 ';
$cal = array();
$cal['1999'] = $year1999;
$cal['2000'] = $year2000;
$cal['2001'] = $year2001;

require_once('XML/RPC2/Value.php');
$cal = XML_RPC2_Value::createFromNative($cal, 'struct');
$result = $client->nestedStructTest($cal);
var_dump($result);

?>
--EXPECT--
int(35)