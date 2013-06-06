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

    }

    private function getConfigForm()
    {
        $form = new Form;
        $form->addClass('form-inline');
        $form->addHidden('command', 'install');
        $form->addHidden('action', 'post_config');
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
