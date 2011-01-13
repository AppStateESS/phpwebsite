<?php

/**
 * This is a modification of the freecap_wrap.php file that comes with
 * Howard Yeend's freecap CAPTCHA zip. You can get more information at
 * http://www.puremango.co.uk/
 * Thank you, Mr. Yeend, for sharing your work.
 *
 * @version $Id$
 * @author Howard Yeend
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */
function verify($return_value=false)
{
    if (!Captcha::isGD()) {
        return false;
    }

    $answer = trim($_POST['captcha']);

    if(!empty($_SESSION['freecap_word_hash'])) {
        // all freeCap words are lowercase.
        // font #4 looks uppercase, but trust me, it's not...

        if($_SESSION['hash_func'](strtolower($answer)) == $_SESSION['freecap_word_hash']) {
            // reset freeCap session vars
            // cannot stress enough how important it is to do this
            // defeats re-use of known image with spoofed session id
            $_SESSION['freecap_attempts'] = 0;
            $_SESSION['freecap_word_hash'] = false;

            // now process form

            // now go somewhere else
            // header("Location: somewhere.php");
            if ($return_value) {
                return $_POST['captcha'];
            } else {
                return true;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}
?>