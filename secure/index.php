<?php
require_once('../config/core/config.php');
// Build new URL
require_once PHPWS_SOURCE_DIR . 'src/Server.php';
$redirect = preg_replace('/secure\/?$/', '', \Canopy\Server::getSiteUrl());
?>
<html>
    <head>
        <!-- THIS FILE SHOULD NEVER EVER BE CACHED.  MAKE SURE TO DISABLE CACHING AT THE APACHE LEVEL. -->
        <meta http-equiv="refresh" content="0;url=<?php echo $redirect; ?>" />
    </head>
    <body>
        <p><a href="<?php echo $redirect; ?>">If you are not redirected automatically, please click this link.</a></p>
    </body>
</html>
