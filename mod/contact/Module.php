<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
namespace contact;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \Module implements \SettingDefaults
{

    public function __construct()
    {
        parent::__construct();
        $this->setTitle('contact');
        $this->setProperName('Contact');
    }

    public function getController(\Request $request)
    {
        $cmd = $request->shiftCommand();
        if ($cmd == 'admin' && \Current_User::allow('contact')) {
            $admin = new \contact\Controller\Admin($this);
            return $admin;
        }
    }

    public function runTime(\Request $request)
    {
    }

    public function getSettingDefaults()
    {
        $settings['room_number'] = null;
        $settings['building'] = null;
        $settings['street'] = null;
        $settings['post_box'] = null;
        $settings['city'] = null;
        $settings['state'] = null;
        $settings['zip'] = null;
        return $settings;
    }

}

?>
