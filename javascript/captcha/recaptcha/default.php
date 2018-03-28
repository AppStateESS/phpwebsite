<?php

$settings_file = PHPWS_SOURCE_DIR . 'javascript/captcha/recaptcha/recaptcha_settings.php';
if (file_exists($settings_file)) {
    require_once($settings_file);
} else {
    throw new \Exception('Recaptcha settings file was not found.');
    exit;
}

$data['site_key'] = RECAPTCHA_PUBLIC_KEY;