<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Install {

    private $setup;

    public function __construct($setup)
    {
        $this->setup = $setup;
    }

    public function get()
    {
        $request = Request::singleton();
        $op = $request->isGetVar('op') ? $request->getGet('op') : 'prompt';

        switch ($op) {
            case 'prompt':
                $this->setup->setTitle(t('New installation'));
                $this->setup->setContent('<p>You do not have a config/core/config.php file. This must be a new installation.</p>
                    <a href="index.php?command=install&op=config_form" class="btn-large btn-primary">Install phpWebSite</a>');
                break;

            case 'config_form':
                $this->setup->setTitle(t('Create database configuration file'));
                $this->getConfigForm();
                break;
        }
    }

    public function post()
    {
        $request = Request::singleton();
        if (!$request->isPostVar('op')) {
            throw new Exception(t('Missing post operation'));
        }

        echo Request::show();
        switch ($request->getPost('op')) {
            case 'post_config':
                $this->postConfig();
                break;
        }
    }

    private function postConfig()
    {
        $dsn = new \Database\DSN($request->getPost('database_type'),
                $request->getPost('database_user'));

        $dsn->setDatabaseName($request->getPost('database_name'));
        $dsn->setHost($request->getPost('database_host'));
        $dsn->setPassword($request->getPost('database_password'));
        if ($request->isPostVar('database_port')) {
            $dsn->setPort($request->getPost('database_port'));
        }
    }

    private function getForm()
    {
        $form = new Form;
        $form->addClass('form-inline');
        $form->addHidden('sec', 'install');
        return $form;
    }

    private function getConfigForm()
    {
        $form = $this->getForm();
        $form->addHidden('op', 'post_config');
        $form->addRadio('database_type',
                array('mysql' => 'MySQL', 'pgsql' => 'PostgreSQL'));
        $form->addTextField('database_name');
        $form->addTextField('database_user');
        $form->addTextField('database_password');
        $form->addTextField('database_host');
        $form->addTextField('database_port');
        $form->addTextField('table_prefix');
        $form->addSubmit('save', t('Create database file'));
        $this->setup->setTitle(t('Create your database configuration file'));
        $this->setup->setContent($form->__toString());
    }

}

?>
