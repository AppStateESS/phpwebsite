<?php

define('SHORTCUT_BAD_KEYWORD',     1);
define('SHORTCUT_WORD_IN_USE',     2);
define('SHORTCUT_MISSING_KEYWORD', 3);
define('SHORTCUT_MISSING_URL',     4);
define('ACCESS_FILES_DIR',         5);
define('ACCESS_HTACCESS_WRITE',    6);
define('ACCESS_HTACCESS_MISSING',  7);

define('DEFAULT_REWRITE_1', 'RewriteRule ^([a-z]+)([0-9]+).html$ index.php?module=$1&id=$2 [L]');
define('DEFAULT_REWRITE_2', 'RewriteRule ^([a-z]+)([0-9]+)_([0-9]+).html$ index.php?module=$1&id=$2&page=$3 [L]');


?>