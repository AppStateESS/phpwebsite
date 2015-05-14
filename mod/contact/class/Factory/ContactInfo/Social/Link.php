<?php

namespace contact\Factory\ContactInfo\Social;

use contact\Resource\ContactInfo\Social\Link as LinkResource;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Link
{

    public static function view($link)
    {
        $url = $link->getUrl();
        $title = $link->getTitle();
        $icon = $link->getFAIcon();
        return <<<EOF
<a href="$url" title="$title" target="_blank">
    $icon
</a>
EOF;
    }

}
