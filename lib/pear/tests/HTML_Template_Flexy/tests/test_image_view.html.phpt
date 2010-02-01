--TEST--
Template Test: image_view.html
--FILE--
<?php
require_once 'testsuite.php';
compilefile('image_view.html');

--EXPECTF--
===Compiling image_view.html===



===Compiled file: image_view.html===


<table cellpadding="2" cellspacing="2" border="0" bgcolor="black" style="text-align: left; width: 100%;">

  <tbody>
    <?php if ($this->options['strict'] || (is_array($t->images)  || is_object($t->images))) foreach($t->images as $row) {?><tr>
      <?php if ($this->options['strict'] || (is_array($row)  || is_object($row))) foreach($row as $col) {?><td align="center" valign="middle" background="<?php echo htmlspecialchars($t->rootURL);?>/FlexyWiki/templates/negative.jpg"><a href="<?php echo htmlspecialchars($col->link);?>"><img border="0" height="<?php echo htmlspecialchars($col->info[1]);?>" width="<?php echo htmlspecialchars($col->info[0]);?>" src="<?php echo htmlspecialchars($col->url);?>"></a><br>
            <font color="white">[<?php echo htmlspecialchars($col->name);?>] <?php echo htmlspecialchars($col->size);?>Mb</font>
      </td><?php }?>
    </tr><?php }?>
  </tbody>
</table>




===With data file: image_view.html===


<table cellpadding="2" cellspacing="2" border="0" bgcolor="black" style="text-align: left; width: 100%;">

  <tbody>
      </tbody>
</table>