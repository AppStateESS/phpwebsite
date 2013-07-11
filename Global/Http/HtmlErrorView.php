<?php

namespace Http;

/**
 * TODO: This should all be handled in templates that are 100% user-editable.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HtmlErrorView extends \Template
{
    public function __construct(\Request $request, \Http\ErrorResponse $response)
    {
        $vars = array();
        $vars['url']       = $request->getUrl();
        $vars['method']    = $request->getMethod();
        $vars['module']    = $request->getModule();
        $vars['code']      = $response->getCode();
        $vars['phrase']    = $response->getPhrase();
        $vars['backtrace'] = $response->getBacktrace();
        $vars['exception'] = $response->getException();

        parent::__construct($vars, PHPWS_SOURCE_DIR . 'Global/Templates/Http/HtmlError.html', false);
    }
}

?>
