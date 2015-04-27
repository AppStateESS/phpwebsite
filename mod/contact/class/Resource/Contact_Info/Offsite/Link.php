<?php

namespace contact\Resource\Contact_Info\Offsite;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Link
{
    /**
     * Name of font awesome icon
     * @var string
     */
    private $icon = null;
    
    /**
     * @var \Variable\TextOnly
     */
    private $title;
    
    /**
     * @var \Variable\Url
     */
    private $url;

    public function __construct()
    {
        $this->title = new \Variable\TextOnly(null, 'title');
        $this->url = new \Variable\Url(null, 'url');
    }

}
