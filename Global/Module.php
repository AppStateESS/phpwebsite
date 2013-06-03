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

    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        if (is_file($this->directory . 'inc/runtime.php')) {
            include $this->directory . 'inc/runtime.php';
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

}

?>
