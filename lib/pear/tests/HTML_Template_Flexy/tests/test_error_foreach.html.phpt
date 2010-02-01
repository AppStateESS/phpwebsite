--TEST--
Template Test: error_foreach.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('error_foreach.html');

--EXPECTF--
===Compiling error_foreach.html===



===Compiled file: error_foreach.html===



<!-- Bugs: 739
<td flexy:foreach="xxxx">xxx</td> 
 {foreach:xxxx} {end:} 
-->



===With data file: error_foreach.html===



<!-- Bugs: 739
<td flexy:foreach="xxxx">xxx</td> 
 {foreach:xxxx} {end:} 
-->