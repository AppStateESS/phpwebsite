<?php

namespace Http;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class RedirectResponse extends \Response
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
        $this->code = $this->getHttpResponseCode();
    }

    public function getUrl()
    {
        return $this->url;
    }

    protected abstract function getHttpResponseCode();

    public function forward()
    {
        header($this->getStatusLine());
        header('Location: ' . $this->getUrl());
        exit(); // TODO: Never ever exit early
    }
}

?>
