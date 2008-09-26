<?php

/**
 * Gets the html for showing the ReCaptcha image
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('recaptcha_settings.php');
require_once('recaptchalib.php');

$error = '';

$default['content'] = recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, $error);

?>
