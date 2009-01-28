<?php

/**
 * Cosign authorization
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class cosign_authorization extends User_Authorization {
    public $create_new_user = true;
    public $show_login_form = true;

    // Enter the url to the cosign login page
    public $login_url       = '';
    public $login_label     = 'Cosign log in';
    public $force_redirect  =  false;

    // Link to the cosign logout
    public $logout_link     = '';

    public function authenticate()
    {
        if (!isset($_SERVER['REMOTE_USER'])) {
            return false;
        }
        return $_SERVER['REMOTE_USER'] == $this->user->username;
    }

    public function forceLogin()
    {
        if (!isset($_SERVER['REMOTE_USER'])) {
            return;
        }

        Current_User::loginUser($_SERVER['REMOTE_USER']);
    }
   
    public function verify()
    {
        if (empty($this->user->username) || empty($_SERVER['REMOTE_USER'])) {
            return false;
        }

        return $this->user->username == $_SERVER['REMOTE_USER'];
    }

    // Run before a new user is created.
    public function createUser(){}

    public function logout(){}
}
?>