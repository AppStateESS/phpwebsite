--TEST--
XML Parser: error class
--SKIPIF--
<?php if (!extension_loaded("xml")) echo 'skip'; ?>
--FILE--
<?php // -*- C++ -*-
//
// Test for: XML/Parser.php
// Parts tested: - parser error class
//
chdir (dirname(__FILE__));
if (file_exists('../Parser.php')) {
    require_once "../Parser.php";
} else {
    require_once "XML/Parser.php";
}

print "new XML_Parser ";
var_dump(strtolower(get_class($p = new XML_Parser())));
$e = $p->parseString("<?xml version='1.0' ?>\n<foo></bar>", true);
if (PEAR::isError($e)) {
    printf("error message: %s\n", $e->getMessage());
} else {
    print "no error\n";
}

?>
--EXPECT--
new XML_Parser string(10) "xml_parser"
error message: XML_Parser: Mismatched tag at XML input line 2:12
