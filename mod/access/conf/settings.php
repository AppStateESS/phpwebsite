<?php
$settings = array('rewrite_engine' => 0,
                  'shortcuts_enabled' = 1,
                  'default_rewrite_1' => '^([a-z]+)([0-9]+).html$ index.php?module=$1&id=$2 [L]',
                  'default_rewrite_2' => '^([a-z]+)([0-9]+)_([0-9]+).html$ index.php?module=$1&id=$2&page=$3 [L]'
                  );
?>
