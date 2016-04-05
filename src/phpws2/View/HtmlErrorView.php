<?php

namespace View;

/**
 * TODO: This should all be handled in templates that are 100% user-editable.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
class HtmlErrorView extends \Template {

    protected $code;

    public function __construct(\Request $request, \Http\ErrorResponse $response)
    {
        $vars = array();
        $vars['url'] = $request->getUrl();
        $vars['method'] = $request->getMethod();
        $vars['module'] = $request->getModule();
        $vars['code'] = $response->getCode();
        $vars['phrase'] = $response->getPhrase();
        $vars['backtrace'] = $response->getBacktrace();
        $vars['exception'] = $response->getException();

        $this->code = $vars['code'];

        parent::__construct($vars,
                PHPWS_SOURCE_DIR . 'Global/Templates/Http/HtmlError.html', false);
    }

    public function render()
    {
        // If not defined, assume the most secure bet
        if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
            return parent::render();
        } else {
            return \Error::errorPage($this->code);
        }
    }

}

?>
