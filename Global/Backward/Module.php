<?php

namespace Backward;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Module extends \ModuleAbstract implements \SettingDefaults {

    public function __construct()
    {
        \Backward::load();
        parent::__construct();
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
        $address = 'mod/' . $this->name . '/index.php';
        include $address;
    }

    public function post()
    {
        $address = 'mod/' . $this->name . '/index.php';
        include $address;
    }

}

?>
