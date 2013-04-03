<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class PHPWS_Error {

    public $message;
    public $code;
    public $mode;
    public $options;
    public $userinfo;
    public $error_class;
    public $skipmsg;

    public static function isError($error)
    {
        return is_a($error, 'PHPWS_Error');
    }

    public static function raiseError($message = null, $code = null, $mode = null, $options = null, $userinfo = null, $error_class = null, $skipmsg = false)
    {
        throw new \Exception($message, $code);
    }

    public static function logIfError($item)
    {
        if (self::isError($item)) {
            throw new \Exception($item->getMessage());
        }
        return true;
    }

}

?>
