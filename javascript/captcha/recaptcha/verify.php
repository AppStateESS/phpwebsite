<?php

/**
 * Function for verifying ReCapthca answers
 * @author Matt McNaney <mcnaneym@appstate.edu>
 */
$settings_file = PHPWS_SOURCE_DIR . 'javascript/captcha/recaptcha/recaptcha_settings.php';
if (file_exists($settings_file)) {
    require_once($settings_file);
} else {
    throw new \Exception('Recaptcha settings file was not found.');
    exit;
}

function verify($return_value = false)
{
    $privateKey = RECAPTCHA_PRIVATE_KEY;
    $remoteIp = $_SERVER['REMOTE_ADDR'];
    $response = filter_input(INPUT_POST, 'g-recaptcha-response',
            FILTER_SANITIZE_ENCODED,
            array('flags' => array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH)));

    if (empty($response)) {
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $privateKey,
        'response' => $response
    );
    $query = http_build_query($data);
    $options = array(
        'http' => array(
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content' => $query
        )
    );
    $context = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);

    $answer = json_decode($verify);
    return $answer->success;
}
