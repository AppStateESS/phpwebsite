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

class convert_authorization extends User_Authorization {
    public $create_new_user = false;
    public $show_login_form = true;

    public function authenticate()
    {
        $db = new PHPWS_DB('users_conversion');
        if (!Current_User::allowUsername($this->user->username)) {
            return FALSE;
        }

        $db->addWhere('username', strtolower($this->user->username));
        $db->addWhere('password', md5($this->password));
        $result = $db->select('one');

        if (PHPWS_Error::logIfError($result) || !$result) {
            return false;
        }

        $db2 = new PHPWS_DB('users');
        $db2->addWhere('username', strtolower($this->user->username));
        $result = $db2->loadObject($this->user);

        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        $this->user->setPassword($this->password);
        $this->user->authorize = LOCAL_AUTHORIZATION;
        $result = $this->user->save();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        return true;
    }

    public function verify()
    {
        return ($this->user->id && $this->user->_logged);
    }

    public function createUser(){}
    public function logout(){}
}


?>