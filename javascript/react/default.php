<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
loadReact($data);
function loadReact($data)
{
    javascript('jquery');
    $home_http = PHPWS_SOURCE_HTTP;
    if (isset($data['development']) && $data['development']) {
        if (isset($data['addons']) && $data['addons']) {
            Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.js'></script>");
        } else {
            Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.js'></script>");
        }
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/JSXTransformer.js'></script>");
    } else {
        if (isset($data['addons']) && $data['addons']) {
            Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.min.js'></script>");
        } else {
            Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.min.js'></script>");
        }
    }
}
