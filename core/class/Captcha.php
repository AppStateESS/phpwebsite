<?php
  /**
   * Small class that assists in loading CAPTCHA routines
   * 
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('CAPTCHA_NAME')) {
    define('CAPTCHA_NAME', '');
 }

class Captcha {

    public function get()
    {
        if (!Captcha::isGD()) {
            return null;
        }

        $dirname = 'captcha/' . CAPTCHA_NAME . '/';

        if (is_dir('javascript/' . $dirname)) {
            return javascript($dirname);
        } else {
            return null;
        }
    }

    public function verify($answer)
    {
        if (!Captcha::isGD()) {
            return true;
        }

        $file = 'javascript/captcha/' . CAPTCHA_NAME . '/verify.php';

        if (!is_file($file)) {
            return true;
        }

        include $file;
        
        if (!function_exists('verify')) {
            return true;
        }

        return verify($answer);
    }

    public function isGD()
    {
        return extension_loaded('gd');
    }

}
?>