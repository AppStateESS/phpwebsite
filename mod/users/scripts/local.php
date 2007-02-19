<?php
/**
 * Local authorization script
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function authorize($user, $password)
{
    $db = new PHPWS_DB('user_authorization');
    if (preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', $user->username)) {
        return FALSE;
    }
    $db->addWhere('username', strtolower($user->username));
    $db->addWhere('password', md5($user->username . $password));
    $result = $db->select('one');

    if (PEAR::isError($result)) {
        return $result;
    } else {
        return isset($result);
    }
}

?>
