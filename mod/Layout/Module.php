<?php

namespace Layout;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \Module implements \SettingDefaults {

    public function init()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/Layout/class/Layout.php';
    }

    public function getSettingDefaults()
    {

    }

}

?>
