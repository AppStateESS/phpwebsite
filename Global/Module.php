<?php

/**
 * Default module class for old phpWebsite modules.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \ModuleAbstract implements \SettingDefaults {

    public $unregister;
    public $register;

    public function run()
    {
        if (is_file($this->directory . 'inc/runtime.php')) {
            include $this->directory . 'inc/runtime.php';
        }
        if (Current_User::isLogged() && is_file($this->directory . 'boost/controlpanel.php')) {
            $this->addPHPWSPanelLinks();
        }
    }

    public function init()
    {
        if (is_file($this->directory . 'inc/init.php')) {
            include $this->directory . 'inc/init.php';
        }
    }

    public function getSettingDefaults()
    {
        /* an array that will inside the settings.php file */
        $settings = null;
        $file_path = 'mod/' . $this->name . '/inc/settings.php';
        if (!is_file($file_path)) {
            throw new \Exception(t('Backward module is missing settings.php file'));
        }

        include $file_path;
        return $settings;
    }

    public function get()
    {
        include $this->directory . 'index.php';
    }

    public function post()
    {
        include $this->directory . 'index.php';
    }

    public function destruct()
    {
        if (is_file($this->directory . 'inc/close.php')) {
            include $this->directory . 'inc/close.php';
        }
    }

    public function setRegister($register)
    {
        $this->register = (bool) $register;
    }

    public function setUnregister($unregister)
    {
        $this->unregister = (bool) $unregister;
    }

    public function loadData()
    {
        parent::loadData();
        $boost_file = $this->directory . 'boost/boost.php';
        if (is_file($boost_file)) {
            include $boost_file;
            $this->file_version = $version;
        }
    }

    protected function addPHPWSPanelLinks()
    {
        include $this->directory . 'boost/controlpanel.php';
        if (!empty($link)) {
            foreach ($link as $l) {
                extract($l);
                $title = t('Options');
                $full_link = <<<EOF
<a href="$url">$title</a>
EOF;
                Controlpanel::getToolbar()->addSiteOption($this->title,
                        $full_link);
            }
        }
    }

}

?>
