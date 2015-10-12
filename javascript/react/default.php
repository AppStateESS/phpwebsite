<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
javascript('jquery');
$home_http = PHPWS_SOURCE_HTTP;
if (isset($data['development']) && $data['development']) {
    if (isset($data['addons']) && $data['addons']) {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.js'></script>", 'drwao');
    } else {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.js'></script>", 'drwoao');
    }
    Layout::addJSHeader("<script src='{$home_http}javascript/react/build/JSXTransformer.js'></script>", 'jsxtrans');
} else {
    if (isset($data['addons']) && $data['addons']) {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.min.js'></script>", 'rwao');
    } else {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.min.js'></script>", 'rwoao');
    }
}