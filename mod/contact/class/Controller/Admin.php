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

    public function getHtmlView($data, \Request $request)
    {
        $command = $request->shiftCommand();
        if (method_exists($this, $command)) {
            $content = $this->$command($request);
        } else {
            $content = $this->form($request);
        }
        \Form::requiredScript();
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }

    public function post(\Request $request)
    {
        $values = $request->getVars();
        $contact_info = new Resource\ContactInfo();

        Factory::postContactInfo($contact_info, $values['vars']);
        $response = new \Http\SeeOtherResponse(\Server::getCurrentUrl(false));
        return $response;
    }

    private function form()
    {
        return Factory::form();
    }

}
