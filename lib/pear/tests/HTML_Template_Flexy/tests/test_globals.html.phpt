--TEST--
Template Test: globals.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('globals.html');

--EXPECTF--
===Compiling globals.html===

===Compile failure==
[pear_error: message="HTML_Template_Flexy fatal error:HTML_Template_Flexy::Attempt to access private variable: on line %d of %s, Use options[privates] to allow this." code=-1 mode=return level=notice prefix="" info=""]