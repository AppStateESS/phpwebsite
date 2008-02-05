<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$embed_name  = 'youtube';
$feed_url    = "http://gdata.youtube.com/feeds/api/videos?vq=";

$title       = "['FEED']['ENTRY'][0]['TITLE']['CONTENT']";
$description = "['FEED']['ENTRY'][0]['CONTENT']['CONTENT']";
$duration    = "['FEED']['ENTRY'][0]['MEDIA:GROUP'][0]['YT:DURATION']['ATTRIBUTES']['SECONDS']";
$thumbnail   = "['FEED']['ENTRY'][0]['MEDIA:GROUP'][0]['MEDIA:THUMBNAIL']['ATTRIBUTES']['URL']";

//Default youtube height and width
$width       = 425;
$height      = 373;

$pull_regexp = '@http://(www.)?youtube.com/.*(\?|&)v=([^&]+)(&.*)?@';
$pull_replace = 3;

?>