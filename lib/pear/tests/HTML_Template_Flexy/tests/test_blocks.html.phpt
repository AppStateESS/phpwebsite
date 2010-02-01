--TEST--
Template Test: blocks.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('blocks.html');

--EXPECTF--
===Compiling blocks.html===



===Compiled file: blocks.html===
<span id="block1">
This is block 1
</span>

<span id="block2">
This is block 2
</span>






===With data file: blocks.html===
<span id="block1">
This is block 1
</span>

<span id="block2">
This is block 2
</span>





