<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function youtube_import($media)
{
        $feed_url = 'http://gdata.youtube.com/feeds/api/videos?vq=';
    if (preg_match('/http:\/\//', $media->file_name)) {
        $pull_regexp = '@http://(www.)?youtube.com/.*(\?|&)v=([^&]+)(&.*)?@';
        $media->file_name = preg_replace($pull_regexp, "\\3", $media->file_name);
    }

    $parse = new XMLParser($feed_url . $media->file_name, false);
    if ($parse->error) {
        \core\Error::log($parse->error);
        return false;
    }
    $parse->setContentOnly(false);
    $info = $parse->format();

    $media->title       = $info['FEED']['ENTRY'][0]['TITLE']['CONTENT'];
    $media->description = $info['FEED']['ENTRY'][0]['CONTENT']['CONTENT'];
    $media->duration    = $info['FEED']['ENTRY'][0]['MEDIA:GROUP'][0]['YT:DURATION']['ATTRIBUTES']['SECONDS'];
    $thumbnail          = $info['FEED']['ENTRY'][0]['MEDIA:GROUP'][0]['MEDIA:THUMBNAIL'][0]['ATTRIBUTES']['URL'];

    if (!empty($thumbnail)) {
        $thumb_name = 'youtube_' . $media->file_name . '.jpg';
        $thumb_dir  = $media->thumbnailDirectory();
        if (!is_dir($thumb_dir)) {
            \core\Error::log(FC_THUMBNAIL_NOT_WRITABLE, 'filecabinet', 'youtube_import', $thumb_dir);
            $media->genericTN($thumb_name);
        } else {
            $thumb_path = $thumb_dir . $thumb_name;

            if (@copy($thumbnail, $thumb_path)) {
                $media->thumbnail = $thumb_name;
            } else {
                $media->genericTN($thumb_name);
            }
        }
    }

    $media->width = 425;
    $media->height = 373;
    return true;
}

?>