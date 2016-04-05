<?php

namespace View;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
class JsonErrorView extends JsonView
{

    public function __construct(\Request $request, \Http\ErrorResponse $response)
    {
        $json = array();
        $json['url'] = $request->getUrl();
        $json['method'] = $request->getMethod();
        $json['module'] = $request->getModule();
        $json['code'] = $response->getCode();
        $json['phrase'] = $response->getPhrase();
        $json['backtrace'] = $response->getBacktrace();
        $json['exception'] = $response->getException();
        if (is_a($json['exception'], '\Exception')) {
            $json['exception_code'] = $response->getException()->getCode();
            $json['exception_file'] = $response->getException()->getFile();
            $json['exception_line'] = $response->getException()->getLine();
            $json['exception_message'] = $response->getException()->getMessage();
        }

        parent::__construct(array('error' => $json));
    }

    public function render()
    {
        if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
            http_response_code($this->data['error']['code']);
            $error = $this->data['error'];
            $this->displayError($error);
            exit;
        } else {
            \Error::errorPage($this->data->error->code);
        }
    }
    
    private function displayError($error)
    {
        echo "url : ", $error['url'];
        echo "\nmethod : ", $error['method'];
        echo "\nmodule : ", $error['module'];
        if (!empty($error['exception'])) {
            echo "\nexception_file : ", $error['exception_file'];
            echo "\nexception_line : ", $error['exception_line'];
            echo "\nexception_message : ", $error['exception_message'];
            echo "\n-------------------------------------\n";
            echo $error['exception']->getTraceAsString();
        }
    }

}

?>
