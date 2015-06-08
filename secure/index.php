<?php
// Detect phpWebSite
if (file_exists('../config/core/config.php')) {
    define('PHPWEBSITE', true);

    require_once('../config/core/config.php');
    require_once(PHPWS_SOURCE_DIR . 'inc/Bootstrap.php');

    if (isset($_SERVER['PHP_AUTH_USER'])) {
        require_once(PHPWS_SOURCE_DIR . 'mod/users/class/Current_User.php');
        Current_User::loginUser(preg_replace(PHPWS_SHIBB_USER_AUTH, '', $_SERVER['PHP_AUTH_USER']));
    }

    PHPWS_unBootstrap();
}

// Build new URL
require_once PHPWS_SOURCE_DIR . 'Global/Server.php';
$redirect = preg_replace('/secure\/?$/', '', \Server::getSiteUrl());
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
