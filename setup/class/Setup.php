<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
define('SITE_HASH', 'x');

class Setup {
    private $template;

    public function initialize()
    {
        Session::start();
    }

    public function display()
    {
        $variables['content'] = 'hi';
        echo new Template($variables, 'setup/templates/index.html');
    }

    public function isAdminLoggedIn()
    {
        return isset(Session::singleton()->admin_logged_in);
    }

    public function showLoginForm()
    {
        $form = new Form;
        $form->addTextField('username');
        $form->addPassword('password');
        $content = $form->printTemplate();
    }
}

?>
