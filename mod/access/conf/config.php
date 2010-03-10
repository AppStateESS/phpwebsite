<?php
/**
 * The rewrite conditions are set in this file. If you alter them
 * MAKE A BACKUP! Writing a bad .htaccess file will cause apache to
 * serve only error pages for your site.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

define('DEFAULT_CONDITION', "RewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d");
define('DEFAULT_REWRITE_1', 'RewriteRule ^([a-z0-9]+)/([a-z0-9]+)/?$ index.php?module=$1&id=$2 [L,NC]');
define('DEFAULT_REWRITE_2', 'RewriteRule ^([a-z0-9]+)/([a-z0-9]+)/([a-z0-9]+)/?$ index.php?module=$1&id=$2&page=$3 [L,NC]');
?>