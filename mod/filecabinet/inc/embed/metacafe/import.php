<?php
function metacafe_import($media)
{
    PHPWS_Core::initCoreClass('XMLParser.php');
    if (preg_match('/http:\/\//', $media->file_name)) {
        $pull_regexp = '@http://www.metacafe.com/watch/(\d+).*@';
        $media->file_name = preg_replace($pull_regexp, "\\1", $media->file_name);
    }

    $feed_url = 'http://www.metacafe.com/api/item/';
    $parse = new XMLParser($feed_url . $media->file_name, false);
    if ($parse->error) {
        PHPWS_Error::log($parse->error);
        return false;
    }
    $parse->setContentOnly(false);
    $info = $parse->format();

    $media->setTitle($info['RSS']['CHANNEL'][0]['ITEM'][0]['TITLE']['CONTENT']);
    $media->setDescription($info['RSS']['CHANNEL'][0]['ITEM'][0]['DESCRIPTION']['CONTENT']);
    $media->duration    = $info['RSS']['CHANNEL'][0]['ITEM'][0]['MEDIA:CONTENT']['ATTRIBUTES']['DURATION'];
    $thumbnail          = $info['RSS']['CHANNEL'][0]['ITEM'][0]['MEDIA:THUMBNAIL']['ATTRIBUTES']['URL'];

    // The height and width below are pulled from the rss feed. Normally, they are a little small. The
    // recommended width and height are hard-coded
    $media->width  = 400;
    $media->height = 345;
    /*
    $media->width       = $info['RSS']['CHANNEL'][0]['ITEM'][0]['MEDIA:CONTENT']['ATTRIBUTES']['WIDTH'];
    $media->height      = $info['RSS']['CHANNEL'][0]['ITEM'][0]['MEDIA:CONTENT']['ATTRIBUTES']['HEIGHT'];
    */

    if (!empty($thumbnail)) {
        $thumb_name = 'metacafe_' . $media->file_name . '.jpg';
        $thumb_dir  = $media->thumbnailDirectory();
        if (!is_dir($thumb_dir)) {
            PHPWS_Error::log(FC_THUMBNAIL_NOT_WRITABLE, 'filecabinet', 'youtube_import', $thumb_dir);
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
    // pulled the id but can't call the video using it alone
    $media->file_name = $info['RSS']['CHANNEL'][0]['ITEM'][0]['MEDIA:CONTENT']['ATTRIBUTES']['URL'];
    return true;
}

?>