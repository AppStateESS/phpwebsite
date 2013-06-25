<?php $errorclass = (int)($code / 100); ?>

<div class="alert alert-error">
<h1>
<?php if($errorclass == 4): ?>
Client Error
<?php elseif($errorclass == 5): ?>
Server Error
<?php else: ?>
Misclassified Response
<?php endif; ?>
</h1>
<p><strong><?php echo "$code $phrase"; ?></strong></p>
<p>For URL <?php echo $url; ?> method <?php echo $method; ?>; backtrace:</p>
<pre><?php echo $backtrace; ?></pre>
<a href="#">Contact the Webmaster</a>
</div>
