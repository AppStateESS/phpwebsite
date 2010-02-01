--TEST--
Template Test: style.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('style.html');

--EXPECTF--
===Compiling style.html===



===Compiled file: style.html===

 



<link rel="stylesheet" type="text/css" media="print" href="<?php echo htmlspecialchars($t->ROOT_CSS);?>/print.css">

<link rel="stylesheet" type="text/css" media="screen" href="<?php echo htmlspecialchars($t->ROOT_CSS);?>/compatible.css">

<style type="text/css" media="screen">

	<!--

		@import url(<?php echo htmlspecialchars($t->ROOT_CSS);?>/main.css);

.tdbodywarningCopy {
	background-color: #eecccc;
	FONT-FAMILY: arial, geneva, helvetica, sans-serif;
	font-size : 10px;
	COLOR: #000000;
	padding: 0px;
	border: 0px dashed #000000;
}

		-->
        
</style>

===With data file: style.html===

 



<link rel="stylesheet" type="text/css" media="print" href="/print.css">

<link rel="stylesheet" type="text/css" media="screen" href="/compatible.css">

<style type="text/css" media="screen">

	<!--

		@import url(/main.css);

.tdbodywarningCopy {
	background-color: #eecccc;
	FONT-FAMILY: arial, geneva, helvetica, sans-serif;
	font-size : 10px;
	COLOR: #000000;
	padding: 0px;
	border: 0px dashed #000000;
}

		-->
        
</style>