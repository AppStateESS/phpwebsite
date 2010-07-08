<?php

/**
 * Gets the html for showing the ReCaptcha image
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . 'javascript/captcha/recaptcha/recaptchalib.php'); // The ReCaptcha library supplied by rechaptcha.net

// Check for recaptcha_settings.php, show an error if it doesn't exist
$settings_file = PHPWS_SOURCE_DIR . 'javascript/captcha/recaptcha/recaptcha_settings.php';
if(file_exists($settings_file)){
    require_once($settings_file);
}else{
    echo "You need to configure ReCaptcha. Look in the file '$settings_file' for more information";
    exit;
}


$error = '';

$default['content']	= recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, $error);
$default['theme']	= RECAPTCHA_THEME;

?>
