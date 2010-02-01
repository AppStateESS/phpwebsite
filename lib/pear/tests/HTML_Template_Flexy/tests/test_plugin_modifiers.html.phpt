--TEST--
Template Test: plugin_modifiers.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('plugin_modifiers.html', 
	array(
		'numbertest' =>  10000.123,
		'datetest' =>  '2004-01-12'
	), 
	array('plugins'=>array('Savant'))
);

compilefile('flexy_raw_with_element.html', 
	array( ), 
	array( )
 
);
--EXPECTF--
<<<<<<< test_plugin_modifiers.html.phpt
=======
===Compiling plugin_modifiers.html===



===Compiled file: plugin_modifiers.html===
<H1>Testing Plugin Modifiers</H1>


<?php echo $this->plugin("dateformat",$t->datetest);?>

<?php echo $this->plugin("numberformat",$t->numbertest);?>


Bug #3946 - inside raw!
 
<input type="checkbox" name="useTextarea3" <?php if ($this->options['strict'] || (isset($t->person) && method_exists($t->person,'useTextarea'))) echo $this->plugin("checked",$t->person->useTextarea());?>>

 

===With data file: plugin_modifiers.html===
<H1>Testing Plugin Modifiers</H1>


12 Jan 2004
10,000.12

Bug #3946 - inside raw!
 
<input type="checkbox" name="useTextarea3" >

 

===Compiling flexy_raw_with_element.html===

Error:/var/svn_live/pear/HTML_Template_Flexy/tests/templates/flexy_raw_with_element.html on Line 5 in Tag &lt;INPUT&gt;:<BR>Flexy:raw can only be used with flexy:ignore, to prevent conversion of html elements to flexy elements>>>>>>> 1.3
