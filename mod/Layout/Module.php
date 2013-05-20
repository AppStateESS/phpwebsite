<?php

namespace Layout;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \ModuleAbstract implements \SettingDefaults {

    public function run()
    {

    }

    public function init()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/Layout/class/Layout.php';
    }

    public function getSettingDefaults()
    {

    }

}

?>