--TEST--
Template Test: flexy_tojavascript.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('flexy_tojavascript.html');

--EXPECTF--
===Compiling flexy_tojavascript.html===



===Compiled file: flexy_tojavascript.html===
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
 
 
<?php require_once 'HTML/Javascript/Convert.php';?>
<script type='text/javascript'>
<?php $__tmp = HTML_Javascript_Convert::convertVar($t->xyz,'test_abc_abcg',true);echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;?>
<?php $__tmp = HTML_Javascript_Convert::convertVar($t->xyz,'test_abc_abcd',true);echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;?>
<?php $__tmp = HTML_Javascript_Convert::convertVar($t->xyz,'test_abc_srcXxx',true);echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;?>
</script>




<?php require_once 'HTML/Javascript/Convert.php';?>
<script type='text/javascript'>
<?php $__tmp = HTML_Javascript_Convert::convertVar($t->xyz,'abcg',true);echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;?>
</script>





<body>
<p>Example of flexy:toJavascript with default values.</p>
</body></html>

===With data file: flexy_tojavascript.html===
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
 
 
<script type='text/javascript'>
test_abc_abcg = null;
test_abc_abcd = null;
test_abc_srcXxx = null;
</script>




<script type='text/javascript'>
abcg = null;
</script>





<body>
<p>Example of flexy:toJavascript with default values.</p>
</body></html>