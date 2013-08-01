<?php
namespace Http;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class RedirectResponse extends \Response {
    private $url;


    public function __construct($url)
    {
        $this->setUrl($url);
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function forward()
    {
        header('location: ' . $this->url);
        exit();
    }
}

?>
