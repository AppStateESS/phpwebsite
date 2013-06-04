<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
define('SITE_HASH', 'x');

class Setup {

    private $page_title;
    private $template;
    private $content;

    public function initialize()
    {
        Session::start();
        $this->page_title = t('phpWebSite Setup');
    }

    public function display()
    {
        $variables['content'] = $this->content;
        $variables['page_title'] = $this->page_title;
        echo new Template($variables, 'setup/templates/index.html');
    }

    public function isAdminLoggedIn()
    {
        return isset(Session::singleton()->admin_logged_in);
    }

    public function login()
    {
        $request = Request::singleton();

        $form = new Form;
        $hidden = $form->addHidden('action', 'login');
        $user = $form->addTextField('username');
        $pass = $form->addPassword('password');

        $user->setLabel(t('Username'));
        $pass->setLabel(t('Password'));
        $form->addSubmit('submit', t('Log In'));
        $this->content = $form->printTemplate('setup/templates/forms/login.html');
    }

    public function processCommand()
    {
        if (!$this->isAdminLoggedIn()) {
            $this->login();
            return;
        }

        $request = Request::singleton();
        switch ($request->getState()) {
            case 'get':
                $this->get();
                break;
            case 'post':
                $this->post();
                break;
        }
    }

    private function get()
    {
        $this->content = 'in get';
    }

    private function post()
    {
        $this->content = 'in post';
    }

}

?>
