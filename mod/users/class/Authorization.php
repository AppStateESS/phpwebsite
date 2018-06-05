<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
abstract class User_Authorization {

    abstract protected function authenticate();

    abstract protected function verify();

    abstract protected function createUser();

    abstract protected function logout();

    /**
     * The user object
     */
    public $user = null;
    public $password = null;

    /**
     * If true, call authenticate function per page load
     */
    public $always_verify = false;

    /**
     * If true, create a new user if none exists locally.
     */
    public $create_new_user = false;

    /**
     * If true, offer a login form
     */
    public $show_login_form = false;

    /**
     * If true, users will force the user to the
     * login_link until they log in
     */
    public $force_login = false;
    public $login_link = null;
    public $login_link_label = null;
    public $logout_link = null;
    public $local_user = true;

    public function __construct(PHPWS_User $user)
    {
        $this->user = $user;
        $this->login_link_label = 'Click here to log in';
    }

    public function showLoginForm()
    {
        return $this->show_login_form;
    }

    public function getLoginLink()
    {
        return sprintf('<a href="%s">%s</a>', $this->login_link,
                $this->login_link_label);
    }

    public function getView()
    {
        $link = $this->login_link;
        $text = 'Log in';
        return <<<EOF
        <a class = "btn btn-default" href = "$link">
        <i class = "fa fa-user"></i> $text
        </a>
EOF;
    }

    public function alwaysVerify()
    {
        return $this->always_verify;
    }

    public function forceLogin()
    {
        if (!$this->force_login) {
            return;
        }
        \phpws\PHPWS_Core::reroute($this->login_link);
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getLogoutLink()
    {
        if ($this->logout_link) {
            return sprintf('<a href="%s"><i class="fa fa-sign-out"></i> %s</a>', $this->logout_link,
                    'Log Out');
        } else {
            return null;
        }
    }

    public function localUser()
    {
        return $this->local_user;
    }

}

