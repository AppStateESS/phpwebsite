--TEST--
DB_mysql::fetchmode object
--SKIPIF--
<?php require "skipif.inc"; ?>
--FILE--
<?php
require 'mktable.inc';
include dirname(__FILE__).'/../fetchmode_object.inc';
?>
--EXPECT--
--- fetch with param DB_FETCHMODE_OBJECT ---
stdClass -> a b c d
stdClass -> a b c d
--- fetch with default fetchmode DB_FETCHMODE_OBJECT ---
stdClass -> a b c d
stdClass -> a b c d
--- fetch with default fetchmode DB_FETCHMODE_OBJECT and class DB_Row ---
db_row -> a b c d
db_row -> a b c d
