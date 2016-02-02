<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
javascript('jquery');

$home_http = PHPWS_SOURCE_HTTP;
if (isset($data['development']) && $data['development']) {
    // if react with addons has already been loaded, we don't also load react.js. With addons has
    // precedence

    // if non dev react was loaded, unset them and let dev take over
    if (isset($GLOBALS['reactDevLoaded'])) {
        return;
    }
    
    if (isset($GLOBALS['reactDevAddOnLoaded'])) {
        return;
    }

    if (isset($data['addons']) && $data['addons']) {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.js'></script>", 'reactload');
        // onload normal react, let addon have precedence
        $GLOBALS['reactDevAddOnLoaded'] = true;
    } else {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.js'></script>", 'reactload');
    }
    $GLOBALS['reactDevLoaded'] = true;
    Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-dom.js'></script>", 'react-dom');
    Layout::addJSHeader("<script src='{$home_http}javascript/react/build/JSXTransformer.js'></script>", 'jsxtrans');
} else {
    // if dev or minified addon react have loaded, don't load again
    if (isset($GLOBALS['reactDevLoaded'])) {
        return;
    }
    
    if (isset($GLOBALS['reactAddOnLoaded'])) {
        return;
    }

    if (isset($data['addons']) && $data['addons']) {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-with-addons.min.js'></script>", 'reactload');
        // onload normal react, let addon have precedence
        $GLOBALS['reactAddOnLoaded'] = true;
    } else {
        Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react.min.js'></script>", 'reactload');
    }
    Layout::addJSHeader("<script src='{$home_http}javascript/react/build/react-dom.min.js'></script>", 'react-dom');
}
