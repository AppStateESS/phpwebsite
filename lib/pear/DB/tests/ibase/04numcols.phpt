--TEST--
DB_ibase::numCols test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
require_once "DB.php";
include("mktable.inc");
include(dirname(__FILE__)."/../numcols.inc");
?>
--EXPECT--
1
2
3
4
