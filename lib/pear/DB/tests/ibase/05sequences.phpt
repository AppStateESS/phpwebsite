--TEST--
DB_driver::sequences
--SKIPIF--
<?php require "skipif.inc"; ?>
--FILE--
<?php
/*
    Test output differs from the standard DB sequences test
    due to a bug in the php interbase driver, that returns
    numbers as strings;
*/
require "connect.inc";
require dirname(__FILE__)."/../sequences.inc";
?>
--EXPECT--
DB Error: unknown error
a=1.
b=2.
b-a=1
c=1.
d=1.
