<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Setup {

    private $page_title;
    private $template;
    private $content;
    private $toolbar;
    private $title;
    private $install;

    public function initialize()
    {
        Session::start();
    }

    public function display()
    {
        if (!empty($this->message)) {
            $variables['message'] = $this->message;
        }
        if (!empty($this->toolbar)) {
            $variables['toolbar'] = $this->toolbar;
        }
        $variables['content'] = $this->content;
        if (!empty($this->title)) {
            $variables['page_title'] = $this->title . ' - ' . t('phpWebSite Setup');
            $variables['title'] = $this->title;
        } else {
            $variables['page_title'] = t('phpWebSite Setup');
        }

        echo new Template($variables, 'setup/templates/index.html');
    }

    public function isAdminLoggedIn()
    {
        return isset(Session::singleton()->admin_logged_in);
    }

    public function login()
    {
        if (!is_file(SETUP_CONFIGURATION_DIRECTORY . 'setup_config.php')) {
            $this->createSetupConfiguration();
            return;
        }
        include SETUP_CONFIGURATION_DIRECTORY . 'setup_config.php';

        $request = Request::singleton();
        $form = new Form;
        $form->addHidden('sec', 'login');
        $user = $form->addTextField('username');
        $pass = $form->addPassword('password');

        $user->setLabel(t('Username'));
        $pass->setLabel(t('Password'));
        $form->addSubmit('submit', t('Log In'));
        $this->content = $form->printTemplate('setup/templates/forms/login.html');

        if ($request->isPostVar('username')) {
            $username_compare = $request->getPost('username');
            if (!$request->isPostVar('password')) {
                throw new Exception(t('Password blank'), SETUP_USER_ERROR);
            }
            $password_compare = $request->getPost('password');
            if (empty($password)) {
                throw new Exception(t('Password blank'), SETUP_USER_ERROR);
            }

            if ($username_compare == $username && $password_compare == $password) {
                Session::singleton()->admin_logged_in = 1;
                header('location: index.php');
                exit();
            }
            throw new Exception(t('Incorrect user name and/or password.'),
            SETUP_USER_ERROR);
        }
    }

    private function createSetupConfiguration()
    {
        $content = new Variable\Arr;
        $content->push('Since this is your first time here, we need to create a setup configuration file.');
        if (!is_writable(SETUP_CONFIGURATION_DIRECTORY)) {
            $content->push('Please make your ' . SETUP_CONFIGURATION_DIRECTORY . ' directory writable.');
            $this->content = $content->implodeTag('<p>');
            return;
        }

        $password = randomString(10);

        $config_body = file_get_contents('setup/templates/setup_config.txt');
        $config_save = "<?php\n" . str_replace('xxx', $password, $config_body) . "\n?>";
        file_put_contents(SETUP_CONFIGURATION_DIRECTORY . 'setup_config.php',
                $config_save);
        $content->push('<strong>Your configuration file has been saved.</strong> Look for a setup_config.php in your ' . SETUP_CONFIGURATION_DIRECTORY . ' directory to get a user name and password to log in.');
        $content->push('We recommend you change both the user name and password.');
        $content->push('You can change the setup_config.php file location by altering the SETUP_CONFIGURATION_DIRECTORY define in the setup/index.php file.');
        $content->push('<a href="index.php">Log in using new user name and password</a>.');
        $this->content = $content->implodeTag('<p>');
    }

    public function processCommand()
    {
        if (!$this->isAdminLoggedIn()) {
            $this->login();
            return;
        }

        $request = Request::singleton();
        if ($request->isPost()) {
            $this->post();
        } else {
            $this->get();
        }
    }

    private function get()
    {
        $request = Request::singleton();

        if (!is_file('config/core/config.php')) {
            $section = 'install';
        } elseif ($request->isGetVar('sec')) {
            $section = $request->getGet('sec');
        } else {
            $section = 'dashboard';
        }

        switch ($section) {
            case 'install':
                $this->loadInstall();
                $this->install->get();
                break;
            case 'dashboard':
                $this->loadToolbar();
                $this->dashboard();
                break;
        }
    }

    private function loadInstall()
    {
        require_once 'setup/class/Install.php';
        $this->install = new Install($this);
    }

    private function postConfigForm()
    {
        $request = Request::singleton();
    }

    private function dashboard()
    {

    }

    private function loadToolbar()
    {

    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    private function post()
    {
        $request = Request::singleton();
        if (!$request->isPostVar('sec')) {
            throw new Exception(t('Missing setup command'));
        }
        $section = $request->getPost('sec');

        switch ($section) {
            case 'install':
                $this->loadInstall();
                $this->install->post();
                break;
        }
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

}

?>
