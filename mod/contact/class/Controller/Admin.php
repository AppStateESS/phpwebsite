<?php

namespace contact\Controller;
use contact\Factory\ContactInfo as Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Admin extends \Http\Controller
{
    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }
    
    public function getHtmlView($data, \Request $request)
    {
        $command = $request->shiftCommand();
        if (method_exists($this, $command)) {
            $content = $this->$command($request);
        } else {
            $content = $this->form($request);
        }
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }
    
    private function form(\Request $request)
    {
        $contact_info = Factory::loadContactInfo();
        return 'loaded';
    }
    
    private function fooBar(\Request $request)
    {
        return 'in foobar';
    }

}
