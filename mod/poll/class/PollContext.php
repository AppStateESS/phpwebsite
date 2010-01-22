<?php

/**
 * Poll CommandContext
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PollContext
{
    private $params = array();
    private $content;

    function __construct()
    {
        foreach($_REQUEST as $key => $val) {
            if(!empty($val)) {
                $this->addParam($key, $val);
            }
        }
    }

    function addParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    function get($key)
    {
        if(!isset($this->params[$key]))
            return NULL;

        return $this->params[$key];
    }

    function plugObject($obj)
    {   
        return PHPWS_Core::plugObject($obj, $this->params);
    }   

    function setDefault($key, $val)
    {   
        if(!isset($this->params[$key]))
            $this->params[$key] = $val;
    }   
}

?>
