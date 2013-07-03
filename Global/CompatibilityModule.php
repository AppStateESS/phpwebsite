<?php

/**
 * Default module class for old phpWebsite modules.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class CompatibilityModule extends \Module implements \SettingDefaults {

    public $unregister;
    public $register;

    public function runTime(\Request $request)
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

    public function getController(\Request $request)
    {
        return $this;
    }

    public function getView(Http\AcceptIterator $iter)
    {
        return new NullView();
    }

    public function execute(\Request $request)
    {
        include $this->directory . 'index.php';
        return new Response(new NullView());
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
}

?>
