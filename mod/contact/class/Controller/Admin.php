<?php

namespace contact\Controller;

use contact\Factory\ContactInfo as Factory;
use contact\Resource;

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

    protected function getHtmlView($data, \Request $request)
    {
        $content = $this->form($request);
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }

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

    private function getGoogleLink(\Request $request)
    {
        $latitude = $request->getVar('latitude');
        $longitude = $request->getVar('longitude');
        $json['url'] = Factory\Map::getImageUrl($latitude, $longitude);
        $response = new \View\JsonView($json);
        return $response;
    }

    private function locationString()
    {
        $json = array();
        $contact_info = Factory::load();
        $physical_address = $contact_info->getPhysicalAddress();

        try {
            $json['address'] = Factory\Map::getGoogleSearchString($physical_address);
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $response = new \View\JsonView($json);
        return $response;
    }

    public function post(\Request $request)
    {
        $command = $request->shiftCommand();

        switch ($command) {
            case 'contactinfo':
                return $this->postContactInfo($request);
                break;
        }
    }

    private function saveThumbnail(\Request $request)
    {
        $latitude = $request->getVar('latitude');
        $longitude = $request->getVar('longitude');

        Factory\Map::createMapThumbnail($latitude, $longitude);
        
        $json['result'] = 'true';
        $response = new \View\JsonView($json);
        return $response;
    }

    private function postContactInfo(\Request $request)
    {
        $values = $request->getVars();
        Factory::post(Factory::load(), $values['vars']);
        $response = new \Http\SeeOtherResponse(\Server::getCurrentUrl(false));
        return $response;
    }

    private function form(\Request $request)
    {
        return Factory::form($request);
    }

}
