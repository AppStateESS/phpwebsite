<?php

namespace controlpanel;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \ModuleAbstract {

    public $unregister;
    public $register;

    public function __construct()
    {
        parent::__construct();
        $this->setTitle('controlpanel');
        $this->setProperName(t('ControlPanel'));
    }

    public function run()
    {

    }

    public function init()
    {
        require_once PHPWS_SOURCE_DIR . 'mod/controlpanel/class/PHPWS_ControlPanel.php';
        require_once PHPWS_SOURCE_DIR . 'mod/controlpanel/class/Controlpanel.php';
    }

    public function get()
    {

    }

    public function post()
    {

    }

    public function destruct()
    {
        if (\Current_User::isLogged()) {
            \Controlpanel::sendToolbarToLayout();
        }
    }

}

?>
