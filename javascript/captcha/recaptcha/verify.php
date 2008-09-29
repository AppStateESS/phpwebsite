<?php

/**
 * Function for verifying ReCapthca answers
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('recaptcha_settings.php');
require_once('recaptchalib.php');

function verify()
{
    if ($_POST["recaptcha_response_field"]) {
        $resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

        if ($resp->is_valid) {
                return TRUE;
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
