<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Message {

    public static function set($message)
    {
        \Session::singleton()->message = $message;
    }

    public static function get()
    {
        if (isset(\Session::singleton()->message)) {
            $message = \Session::singleton()->message;
        } else {
            $message = null;
        }
        unset(\Session::singleton()->message);
        return $message;
    }

    public static function forward($message, $url)
    {
        self::set($message);
        header('location: ' . $url);
        exit();
    }

}

?>
