--TEST--
XML Serializer - test for Bug #12139:  XML_Parser read '#' in data as Invalid document end
--FILE--
<?php
require_once 'XML/Unserializer.php';

$xml = <<<EOF
<stdClass>
  <foo>Some text with #sign in it</foo>
</stdClass>
EOF;

$unserializer = &new XML_Unserializer();
$status = $unserializer->unserialize($xml);
if (PEAR::isError($status)) {
    echo $status->getMessage();
}
$plan = $unserializer->getUnserializedData();
var_dump($plan);
?>
--EXPECT--
array(1) {
  ["foo"]=>
  string(26) "Some text with #sign in it"
}

