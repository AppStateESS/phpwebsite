<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Response
{
    protected $data;
    protected $viewConfig;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setViewForType($mimetype, View $view)
    {
        // TODO: Validate Mime Type
        $this->viewConfig[$mimetype] = $view;
    }

    public function hasViewForType($mimetype)
    {
        return array_key_exists($mimetype, $this->viewConfig);
    }

    public function getViewForType($mimetype)
    {
        if(!$this->hasViewForType($mimetype)) {
            // TODO: Better Exception
            throw new Exception("No view has been configured for type $mimetype");
        }

        return $this->viewConfig[$mimetype];
    }
}

?>
