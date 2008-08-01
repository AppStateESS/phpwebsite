<?php

  /**
   * Conversion login script
   *
   * In phpwebsite 0.x, the password was simply a md5 hash.
   * phpwebsite 1.x salts the password with the user name.
   * To prevent everyone from having to re-enter their password
   * after upgrading, this login script handles the conversion.
   *
   * After conversion, all the usernames and passwords are copied
   * to a users_conversion table. The FIRST time the user logs in
   * this script salts their password and copies it to the main
   * authorization table. It then removes their login from the
   * conversion table. Once the conversion table is empty, this file
   * and the conversion table may be deleted.
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function authorize(PHPWS_User $user, $password)
{
    $db = new PHPWS_DB('users_conversion');
    if (preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', $user->username)) {
        return FALSE;
    }

    $db->addWhere('username', strtolower($user->username));
    $db->addWhere('password', md5($password));
    $result = $db->select('one');

    if (PEAR::isError($result) || !$result) {
        return $result;
    }

    $db2 = new PHPWS_DB('users');
    $db2->addWhere('username', strtolower($user->username));
    $result = $db2->loadObject($user);

    if (PEAR::isError($result)) {
        return $result;
    }

    $user->setPassword($password);
    $user->authorize = LOCAL_AUTHORIZATION;
    $result = $user->save();
    if (PEAR::isError($result)) {
        return $result;
    }

    return $db->delete();
}


?>