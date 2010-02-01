--TEST--
Template Test: conditions.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('conditions.html');

--EXPECTF--
===Compiling conditions.html===



===Compiled file: conditions.html===


<H2>Conditions</H2>
<p>a condition <?php if ($t->condition)  {?> hello <?php } else {?> world <?php }?></p>
<p>a negative condition <?php if (!$t->condition)  {?> hello <?php } else {?> world <?php }?></p>
<p>a conditional method <?php if ($this->options['strict'] || (isset($t) && method_exists($t,'condition'))) if ($t->condition()) { ?> hello <?php } else {?> world <?php }?></p>
<p>a negative conditional method <?php if ($this->options['strict'] || (isset($t) && method_exists($t,'condition'))) if (!$t->condition()) { ?> hello <?php } else {?> world <?php }?></p>


<?php if ($t->test)  {?><span>test</span><?php }?>
<?php if ($this->options['strict'] || (isset($t) && method_exists($t,'test'))) if ($t->test()) { ?><span>test</span><?php }?>
<?php if ($this->options['strict'] || (isset($t) && method_exists($t,'test'))) if ($t->test("aaa bbb",$t->ccc,"asdfasdf asdf ")) { ?><span>test</span><?php }?>



<H2>Notices and errros</H2>


<?php if ($t->notices)  {?><table class="tablenotice" width="70%" align="center">
 
  <tbody>
    <tr>
      <td class="tdheadernotice" height="20" width="526">Thanks</td>
    </tr>
    <tr>
      <td class="tdbodynotice" height="33">
      <?php if ($t->notices['ok'])  {?><p>Submitted data is ok</p><?php }?>
      </td>
    </tr>
  </tbody>
</table><?php }?>
 
<?php if ($t->errors)  {?><table class="tablewarning" width="100%">
  <tbody>
    <tr>
      <td class="tdheaderwarning" height="20" width="526">Sorry</td>
    </tr>
    <tr>
      <td class="tdbodywarning">
        
      <?php if ($t->errors['lastname'])  {?><li>Please fill in your last name</li><?php }?>
   
      </td>
    </tr>
  </tbody>
</table><?php }?>


===With data file: conditions.html===


<H2>Conditions</H2>
<p>a condition  world </p>
<p>a negative condition  hello </p>
<p>a conditional method </p>
<p>a negative conditional method </p>





<H2>Notices and errros</H2>