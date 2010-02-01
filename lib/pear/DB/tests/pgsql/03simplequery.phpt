--TEST--
DB_pgsql::simpleQuery test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include("../simplequery.inc");
?>
--EXPECT--
resource
