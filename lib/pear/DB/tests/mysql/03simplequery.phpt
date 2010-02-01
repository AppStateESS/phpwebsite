--TEST--
DB_mysql::simpleQuery test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include(dirname(__FILE__)."/../simplequery.inc");
?>
--EXPECT--
resource
