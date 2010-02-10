<?php

/**
 * Function for verifying ReCapthca answers
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('recaptchalib.php');

// Check for recaptcha_settings.php, show an error if it doesn't exist
$settings_file = './javascript/captcha/recaptcha/recaptcha_settings.php';
if(file_exists($settings_file)){
    require_once($settings_file);
}else{
    echo "You need to configure ReCaptcha. Look in the file '$settings_file' for more information";
    exit;
}

function verify($return_value=false)
{
    if ($_POST["recaptcha_response_field"]) {
        $resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

        if ($resp->is_valid) {
            // return the words entered
            if ($return_value) {
                return $_POST['recaptcha_response_field'];
            } else {
                return TRUE;
            }
        } else {
            # set the error code so that we can display it
            $_SESSION['recaptcha_error'] = $resp->error;
            return FALSE;
        }

        // Just return false if nothing was entered
        return FALSE;
    }
}

?>
