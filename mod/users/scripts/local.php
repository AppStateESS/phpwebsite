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
    public $always_verify = false;
    public $force_login = false;
    public $login_link = 'index.php?module=users&amp;action=user&amp;command=login_page';
    public $logout_link = 'index.php?module=users&amp;action=user&amp;command=logout';

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

        return (!PHPWS_Error::logIfError($result) && (bool) $result);
    }

    public function verify()
    {
        return ($this->user->id && $this->user->_logged);
    }

    public function getView()
    {
        $text = dgettext('users', 'Sign in');
        return <<<EOF
<a id = "sign-in" data-toggle = "modal" href = "#user-login-modal"><i class = "fa fa-user"></i> $text</a>
<div id="user-signin">
    <div class = "modal fade" id="user-login-modal" tabindex = "-1" role = "dialog" aria-labelledby = "userLoginModalLabel" aria-hidden = "true">
        <div id="user-login-dialog" class = "modal-dialog" style="max-width:50%; min-width:25%;">
            <div class = "modal-content">
                <div class = "modal-body">
                    <form method = "post" action = "index.php">
                        <input type = "hidden" name = "module" value = "users" />
                        <input type = "hidden" name = "action" value = "user" />
                        <input type = "hidden" name = "command" value = "login" />
                        <div class = "form-group">
                            <input type = "text" id = "phpws-username" name = "phpws_username" placeholder = "Username" class = "form-control" />
                        </div>
                        <div class = "form-group">
                            <input type = "password" name = "phpws_password" placeholder = "Password" class = "form-control" />
                        </div>
                        <input type = "submit" class = "btn btn-primary" value = "Sign in" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
EOF;
    }

    public function createUser()
    {

    }

    public function logout()
    {

    }

}

?>
