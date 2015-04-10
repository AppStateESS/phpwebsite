<?php

/**
 * Cosign authorization
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class cosign_authorization extends User_Authorization {
    public $create_new_user = true;
    public $show_login_form = false;

    // Enter the url to the cosign login page
    public $login_link       = '';
    public $login_link_label = 'ASU WebLogin';
    public $force_redirect   = false;

    // Link to the cosign logout
    public $logout_link     = '';

    public function __construct(PHPWS_User $user)
    {
        parent::__construct($user);
        $this->login_link = $this->getLocalCosignLink('login');
        $this->login_link_label = 'ASU WebLogin';
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


    public function getLoginLink()
    {
        return sprintf('<a href="%s"><img src="%s/mod/users/img/external-login.png">%s</a>',
            $this->login_link,
            PHPWS_SOURCE_HTTP,
            $this->login_link_label);
    }

    public function logout()
    {
        setCookie("cosign-{$_SERVER['SERVER_NAME']}", '');
        PHPWS_Core::killAllSessions();
        $this->user->_logged = 0;
        if(!defined('COSIGN_LOGOUT_URL')) {
            PHPWS_Error::log('COSIGN_LOGOUT_URL is not set in core/conf/defines.php');
            PHPWS_Core::errorPage();
        } else {
            PHPWS_Core::reroute(COSIGN_LOGOUT_URL);
        }
    }

    private function getLocalCosignLink($append = 'login')
    {
        // If we're an approved branch, then we should know the URL.
        if(isset($_SESSION['Approved_Branch'])) {
            $url = $_SESSION['Approved_Branch']['url'];

            // Clean it up: HTTP
            if(strtolower(substr($url, 0, 7)) != 'http://' && strtolower(substr($url, 0, 8)) != 'https://') {
	      $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http') . '://' . $url;
            }

            // Clean it up: Trailing Slash
            if(substr($url, sizeof($url) - 1, 1) != '/') {
                $url = $url . '/';
            }

            // Add Append
            $url .= $append;

            return $url;
        }

        // Otherwise, use PHPWS_SOURCE_HTTP.
        return PHPWS_SOURCE_HTTP . $append;
    }
}
?>
