<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandContext
{
    protected $params = array();
    protected $rewritten = false;
    protected $error;
    protected $view;

    function __construct()
    {
        foreach($_REQUEST as $key => $val) {
            if(!empty($val)) {
                $this->set($key, $val);
            }
        }

        if(!isset($_SERVER['REDIRECT_URL'])) $this->rewritten = FALSE;
        else if(empty($_SERVER['QUERY_STRING'])) $this->rewritten = TRUE;
        else $this->rewritten = FALSE;
    }

    function set($key, $val)
    {
        $this->params[$key] = $val;
    }

    function setDefault($key, $val)
    {
        if(!isset($this->params[$key]))
            $this->params[$key] = $val;
    }

    function get($key)
    {
        if(!isset($this->params[$key]))
            return NULL;

        return $this->params[$key];
    }

    function plugObject(PluggableObject $obj)
    {
        $obj->plug($this->params);
    }
}

?>
