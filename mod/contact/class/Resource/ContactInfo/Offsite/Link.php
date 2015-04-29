<?php

namespace contact\Resource\Contact_Info\Offsite;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Link extends \Data
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

    public function view()
    {
        return <<<EOF
<a href="$this->url" title="$this->title" target="_blank">
    <i class="fa fa-$this->icon fa-stack-2x"></i>
</a>
EOF;
    }

}
