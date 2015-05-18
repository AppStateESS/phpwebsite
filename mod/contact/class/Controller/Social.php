<?php

namespace contact\Controller;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Social extends \Http\Controller
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
        $content = \contact\Factory\ContactInfo::form($request, 'social');
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($content));
        return $view;
    }
    
    public function post(\Request $request)
    {
        $social_links = \contact\Factory\ContactInfo\Social::pullSavedLinks();
        $label = $request->getVar('label');
        $url = $request->getVar('url');
        $social_links[$label] = $url;
        \contact\Factory\ContactInfo\Social::saveLinks($social_links);
        echo 'post successful';
        exit;
    }

}
