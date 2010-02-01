--TEST--
Template Test: variables.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('variables.html');

--EXPECTF--
===Compiling variables.html===



===Compiled file: variables.html===
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
 
<body>
<p>Example Template for HTML_Template_Flexy</p>
 
<h2>Variables</H2>

<p>Standard variables 
<?php echo htmlspecialchars($t->hello);?> 
<?php echo $t->world;?>
<?php echo urlencode($t->test);?>
<?php echo htmlspecialchars($t->object->var);?>
<?php echo htmlspecialchars($t->array[0]);?>
<?php echo htmlspecialchars($t->array['entry']);?>
<?php echo htmlspecialchars($t->multi['array'][0]);?>
<?php echo htmlspecialchars($t->object->var['array'][1]);?>
<?php echo '<pre>'; echo htmlspecialchars(print_r($t->object->var['array'][1],true)); echo '</pre>';;?>
<?php echo $t->object->var['array'][1];?>
<?php echo $t->object->var['array'][-1];?>
<?php echo htmlspecialchars($t->object['array']->with['objects']);?>
Long string with NL2BR + HTMLSPECIALCHARS
<?php echo nl2br(htmlspecialchars($t->longstring));?>

Everything: <?php echo '<pre>'; echo htmlspecialchars(print_r($t,true)); echo '</pre>';;?>
an Object: <?php echo '<pre>'; echo htmlspecialchars(print_r($t->object,true)); echo '</pre>';;?>


<img src="<?php echo htmlspecialchars($t->getImageDir);?>/someimage.jpg">
<img src="<?php echo $t->getImageDir;?>/someimage.jpg">
<img src="<?php echo urlencode($t->getImageDir);?>/someimage.jpg">

<img src="<?php echo htmlspecialchars($t->getImageDir);?>/someimage.jpg">
<img src="<?php echo htmlspecialchars($t->getImageDir);?>/someimage.jpg">
</p>
</body>
</html>


===With data file: variables.html===
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
 
<body>
<p>Example Template for HTML_Template_Flexy</p>
 
<h2>Variables</H2>

<p>Standard variables 
 
<pre></pre>Long string with NL2BR + HTMLSPECIALCHARS

Everything: <pre>stdClass Object
(
)
</pre>an Object: <pre></pre>

<img src="/someimage.jpg">
<img src="/someimage.jpg">
<img src="/someimage.jpg">

<img src="/someimage.jpg">
<img src="/someimage.jpg">
</p>
</body>
</html>