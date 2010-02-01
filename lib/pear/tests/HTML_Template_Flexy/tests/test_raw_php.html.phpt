--TEST--
Template Test: raw_php.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('raw_php.html');
compilefile('raw_php.html',array(), array('allowPHP'=>true));
compilefile('raw_php.html',array(), array('allowPHP'=>'delete'));


--EXPECTF--
===Compiling raw_php.html===

===Compile failure==
[pear_error: message="HTML_Template_Flexy fatal error:PHP code found in script (Token)" code=-1 mode=return level=notice prefix="" info=""]


===Compiling raw_php.html===



===Compiled file: raw_php.html===

<? for($i=0;$i<10;$i++) { ?>
number: <?=$i?>
<?php } ?>

<script language="php">

for($i=0;$i<10;$i++) { 

echo "hello world\n";
}
</script>


===With data file: raw_php.html===

number: 0number: 1number: 2number: 3number: 4number: 5number: 6number: 7number: 8number: 9
hello world
hello world
hello world
hello world
hello world
hello world
hello world
hello world
hello world
hello world


===Compiling raw_php.html===



===Compiled file: raw_php.html===


number: 





===With data file: raw_php.html===


number: