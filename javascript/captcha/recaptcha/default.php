<?php

/**
 * Gets the html for showing the ReCaptcha image
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('recaptchalib.php'); // The ReCaptcha library supplied by rechaptcha.net

// Check for recaptcha_settings.php, show an error if it doesn't exist
if(file_exists('recaptcha_settings.php')){
    require_once('recaptcha_settings.php');
}else{
    echo 'You need to configure ReCaptcha. Look in the file \'recaptcha_settings.dist.php\' for more information';
    exit;
}


$error = '';

$default['content']	= recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, $error);
$default['theme']	= RECAPTCHA_THEME;

?>
