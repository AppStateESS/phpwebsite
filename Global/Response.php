<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Response
{
    protected $code;
    protected $view;

    public function __construct(View $view, $code = 200)
    {
        $this->view = $view;
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getPhrase()
    {
        return get_status_text($this->getCode());
    }

    public function getHttpVersion()
    {
        return 'HTTP/1.1';
    }

    public function getStatusLine()
    {
        return $this->getHttpVersion() . ' ' . 
               $this->getCode() . ' ' . 
               $this->getPhrase();
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView(View $view)
    {
        $this->view = $view;
    }
}

?>
