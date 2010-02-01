--TEST--
DB_oci8::numCols test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
include("../numcols.inc");
?>
--EXPECT--
1
2
3
4
