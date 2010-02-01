--TEST--
DB_oci8::simpleQuery test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include("../simplequery.inc");
?>
--EXPECT--
resource
