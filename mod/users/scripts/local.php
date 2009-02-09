<?php
/**
 * Local authorization script
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class local_authorization extends User_Authorization {
        public $create_new_user = true;
        public $show_login_form = true;
        // Authorize on local database just once
        public $always_verify   = false;
        public $force_login     = false;
        public $login_link      = 'index.php?module=users&action=user&command=login_page';
        public $logout_link     = 'index.php?module=users&action=user&command=logout';

    public function authenticate()
    {
        if (empty($this->password)) {
            return false;
        }
        $db = new PHPWS_DB('user_authorization');
        if (!Current_User::allowUsername($this->user->username)) {
            return false;
        }

        $password_hash = md5($this->user->username . $this->password);
        $db->addColumn('username');
        $db->addWhere('username', strtolower($this->user->username));
        $db->addWhere('password', $password_hash);
        $result = $db->select('one');

        return (!PHPWS_Error::logIfError($result) && (bool)$result);
    }

    public function verify()
    {
        return ($this->user->id && $this->user->_logged);
    }

    public function createUser(){}
    public function logout(){}
}
?>
