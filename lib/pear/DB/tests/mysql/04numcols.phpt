--TEST--
DB_mysql::numCols test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include(dirname(__FILE__)."/../numcols.inc");
?>
--EXPECT--
1
2
3
4
