<?php

/**
 * Standard JSON View.  Uses json_encode to encode the given data as JSON, 
 * regardless of what the data is.  Upshot: you get RESTful API for free; 
 * downside: you can only control the JSON format by the data that is passed in.  
 * If you need to override the behavior of this view, create a new class that 
 * implements the View interface and return it from the getJsonView() function 
 * in your Http\Controller, or from the getView() function in your Controller 
 * implementation.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class JsonView implements View
{
    protected $data;
    protected $options;
    protected $depth;

    /**
     * Instantiate JsonView.  Options are analogous to the parameters to the 
     * json_encode function found in PECL json.  Technically this object is just 
     * Command pattern as it allows you to pass around a call to json_encode as 
     * you would a View object.
     *
     * @param $options int See documentation for json_encode.
     * @param $depth int See documentation for json_encode.
     */
    public function __construct($data, $options = 0, $depth = 512)
    {
        $this->data = $data;
        $this->options = $options;
        $this->depth = $depth;
    }

    /**
     * Render as JSON.  This function essentially returns the result of a PECL 
     * json json_encode.
     *
     * @param $data mixed The data to render
     * @return string The JSON encoded data
     */
    public function render()
    {
        return json_encode($this->data);
    }

    public function getContentType()
    {
        return 'application/json';
    }
}

?>
