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
        }
    }

    private function locationString()
    {
        $contact_info = Factory::fetchContactInfo();
        $physical_address = $contact_info->getPhysicalAddress();

        $address_array = Factory\Map::getGoogleSearchString($physical_address);

        $json = $address_array;
        $response = new \View\JsonView($json);
        return $response;

        return $thumbnail;
    }

    public function post(\Request $request)
    {
        $command = $request->shiftCommand();

        switch ($command) {
            case 'contactinfo':
                return $this->postContactInfo($request);
                break;

            case 'saveThumbnail':
                return $this->saveThumbnail($request);
                break;
        }
    }

    private function saveThumbnail(\Request $request)
    {
        $google_lat_long = $request->getVar('google_lat_long');
        $google_url = \contact\Factory\ContactInfo\Map::getImageUrl($google_lat_long);
        $curl = \curl_init($google_url);

        $filename = PHPWS_HOME_DIR . 'images/contact/googlemap_' . time() . '.png';
        $fp = fopen($filename, "w");
        \curl_setopt($curl, CURLOPT_FILE, $fp);
        \curl_setopt($curl, CURLOPT_HEADER, 0);

        \curl_exec($curl);
        \curl_close($curl);
        fclose($fp);
        \Settings::set('thumbnail_map', $filename);
        \Settings::set('lat_long', $google_lat_long);
        $response = new \Http\TemporaryRedirectResponse('contact/admin/map');
        return $response;
    }

    private function postContactInfo(\Request $request)
    {
        $values = $request->getVars();
        $contact_info = new Resource\ContactInfo();

        Factory::postContactInfo($contact_info, $values['vars']);
        $response = new \Http\SeeOtherResponse(\Server::getCurrentUrl(false));
        return $response;
    }

    private function form(\Request $request)
    {
        return Factory::form($request);
    }

}
