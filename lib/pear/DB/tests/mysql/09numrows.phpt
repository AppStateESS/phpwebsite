--TEST--
DB_mysql::numRows test
--SKIPIF--
<?php require "skipif.inc"; ?>
--FILE--
<?php
require "connect.inc";
require "mktable.inc";
require dirname(__FILE__)."/../numrows.inc";
?>
--EXPECT--
1
2
3
4
5
6
2
0
