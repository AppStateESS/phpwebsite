<?php

namespace contact\Controller;

use contact\Factory\ContactInfo\Map as Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Map extends \Http\Controller
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
        $content = \contact\Factory\ContactInfo::form($request, 'map');
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
        $json['url'] = Factory::getImageUrl($latitude, $longitude);
        $response = new \View\JsonView($json);
        return $response;
    }

    private function locationString()
    {
        $json = array();
        $contact_info = \contact\Factory\ContactInfo::load();
        $physical_address = $contact_info->getPhysicalAddress();

        try {
            $json['address'] = Factory::getGoogleSearchString($physical_address);
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $response = new \View\JsonView($json);
        return $response;
    }

    private function saveThumbnail(\Request $request)
    {
        $latitude = $request->getVar('latitude');
        $longitude = $request->getVar('longitude');

        Factory::createMapThumbnail($latitude, $longitude);

        $json['result'] = 'true';
        $response = new \View\JsonView($json);
        return $response;
    }

}
