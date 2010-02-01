--TEST--
XML Parser:  test for Bug #9328 "assigned by reference error in XML_RSS parse"
--FILE--
<?php
/*
 * this issue only exists in PHP4
 */

require_once 'XML/RSS.php';

$url = 'www.someverybogusurl.com';
$rss =& new XML_RSS($url);

$error = $rss->parse();
echo $error->getMessage() . PHP_EOL;
?>
--EXPECT--
XML_Parser: Invalid document end at XML input line 1:1
