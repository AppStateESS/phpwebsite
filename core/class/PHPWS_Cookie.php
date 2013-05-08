<?php

/**
 * Write, reads, and deletes cookies under one site index
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

define('COOKIE_HASH', md5(SITE_HASH . $_SERVER['HTTP_HOST']));

class PHPWS_Cookie {

    public static function write($name, $value, $time=0)
    {
        if (empty($time)) {
            $time = time() + 31536000;
        }
        $time = (int)$time;

        $cookie_index = sprintf('%s[%s]', COOKIE_HASH, $name);
        if (!setcookie($cookie_index, $value, $time)) {
            return PHPWS_Error::get(COOKIE_SET_FAILED, 'core', 'PHPWS_Cookie::write');
        }
    }

    public static function read($name)
    {
        if (isset($_COOKIE[COOKIE_HASH][$name])) {
            return $_COOKIE[COOKIE_HASH][$name];
        } else {
            return NULL;
        }
    }

    public static function delete($name)
    {
        $cookie_index = sprintf('%s[%s]', COOKIE_HASH, $name);
        setcookie($cookie_index, '', time() - 3600);
        if (isset($_COOKIE[COOKIE_HASH]) && is_array($_COOKIE[COOKIE_HASH]) && isset($_COOKIE[COOKIE_HASH][$name])) {
            unset($_COOKIE[COOKIE_HASH][$name]);
        }
    }
}

?>