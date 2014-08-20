<?php

// Detect phpWebSite
if(file_exists('../config/core/config.php')) {
    define('PHPWEBSITE', true);

    require_once('../config/core/config.php');
    require_once(PHPWS_SOURCE_DIR . 'inc/Bootstrap.php');

    session_name(md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
}
session_start();
unset($_SESSION);
session_destroy();


// Shibboleth local logout is always relative to the root
$shiblocallogout = 'https://' . $_SERVER['HTTP_HOST'] . '/Shibboleth.sso/Logout';

// Our destination, however, not necessarily so figure it out
$parts = explode('/', $_SERVER['SCRIPT_URL']);
while(array_pop($parts) != 'secure');
$destination = 'http://' . $_SERVER['HTTP_HOST'] . implode('/', $parts);

if(isset($_SERVER['AUTH_TYPE'])) {
    if(strtolower($_SERVER['AUTH_TYPE']) == 'shibboleth') {
?>
<html>
    <head>
        <meta http-equiv="refresh" content="2;url=<?php echo $destination; ?>" />
    </head>
    <body>
    <p>Logging you out...</p>
    <p><a href="<?php echo $destination; ?>">If you are not redirected automatically, please click this link.</a></p>
    <iframe style="display: none" src="<?php echo $_SERVER['HTTP_SHIB_LOGOUTURL']; ?>?return_url=<?php echo $shiblocallogout?>"><p>Logging You Out...</p></iframe>
    </body>
</html>
<?php
    } else if(strtolower($_SERVER['AUTH_TYPE']) == 'cosign') {
?>
<html>
    <head>
        <meta http-equiv="refresh" content="2; url=<?php echo COSIGN_LOGOUT_URL; ?>" />
    </head>
    <body>
    <p>Logging you out...</p>
    <p><a href="<?php echo COSIGN_LOGOUT_URL; ?>">If you are not redirected automatically, please click this link.</a></p>
    </body>
</html>
<?php
    }
}
?>
