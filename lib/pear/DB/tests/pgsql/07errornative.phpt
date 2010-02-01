--TEST--
DB_pgsql::errorNative test
--SKIPIF--
<?php include("skipif.inc"); ?>
--FILE--
<?php
include("mktable.inc");
$dbh->query("syntax error please");
print $dbh->errorNative() . "\n";
?>
--EXPECT--
ERROR:  parser: parse error at or near "syntax"
