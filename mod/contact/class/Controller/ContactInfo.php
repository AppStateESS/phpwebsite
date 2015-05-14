<?php

namespace contact\Controller;

use contact\Factory\ContactInfo as Factory;
use contact\Resource;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ContactInfo extends \Http\Controller
{
    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    protected function getHtmlView($data, \Request $request)
    {
        $content = Factory::form($request, 'contact_info');
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }

    /*
    protected function getJsonView($data, \Request $request)
    {
        $command = $request->shiftCommand();
        switch ($command) {
            case 'locationString':
                return $this->locationString();
                break;

            case 'getGoogleLink':
                return $this->getGoogleLink($request);
                break;

            case 'saveThumbnail':
                return $this->saveThumbnail($request);
                break;
        }
    }
     * 
     */

    

    public function post(\Request $request)
    {
        $command = $request->shiftCommand();

        switch ($command) {
            case 'contactinfo':
                return $this->postContactInfo($request);
                break;
        }
    }

    

    private function postContactInfo(\Request $request)
    {
        $values = $request->getVars();
        Factory::post(Factory::load(), $values['vars']);
        $response = new \Http\SeeOtherResponse(\Server::getCurrentUrl(false));
        return $response;
    }
}
