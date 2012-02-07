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

    public function __construct(PHPWS_User $user)
    {
        parent::__construct($user);
        $login_url = $this->getLocalCosignLink('login');
    }

    public function authenticate()
    {
        if (!isset($_SERVER['REMOTE_USER'])) {
            return false;
        }

        if(strtolower($_SERVER['REMOTE_USER']) != strtolower($this->user->username)) {
            return false;
        }

        if(!Current_User::allowUsername($this->user->username)) {
            return false;
        }

        return true;
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

        return strtolower($this->user->username) == strtolower($_SERVER['REMOTE_USER']);
    }

    // Run before a new user is created.
    public function createUser(){}

    public function logout()
    {
        setCookie("cosign-{$_SERVER['SERVER_NAME']}", '');
        PHPWS_Core::killAllSessions();
        $this->user->_logged = 0;
        PHPWS_Core::reroute('https://cosign.example.com/cosign-bin/logout');
    }

    private function getLocalCosignLink($append = 'login')
    {
        $parts = explode('/', $_SERVER['REQUEST_URI']);

        reset($parts);
        array_pop($parts);

        $path = '/';
        foreach($parts as $part) {
            if(empty($part)) continue;
            $path .= "$part/";
        }
        $path .= $append;

        return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $path;
    }
}
?>
