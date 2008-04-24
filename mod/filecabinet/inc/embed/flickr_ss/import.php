<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function flickr_ss_import(&$media)
{
    PHPWS_Core::initCoreClass('XMLParser.php');
    $feed_url = "http://api.flickr.com/services/feeds/photos_public.gne?id=";
    $parse = new XMLParser($feed_url . $media->file_name, false);

    if ($parse->error) {
        PHPWS_Error::log($parse->error);
        return false;
    }
    $parse->setContentOnly(false);
    $info = $parse->format();

    $media->title       = sprintf(dgettext('comments', '%s Slide Show'), $info['FEED']['ENTRY'][0]['AUTHOR'][0]['NAME']['CONTENT']);
    $thumbnail          = $info['FEED']['ICON']['CONTENT'];

    if (!empty($thumbnail)) {
        $thumb_name = $media->file_name . '.jpg';
        $thumb_path = $media->thumbnailDirectory() . $thumb_name;
        if (@copy($thumbnail, $thumb_path)) {
            $media->thumbnail = $thumb_name;
        }
    }

    if (empty($media->thumbnail)) {
        $thumb_name = $media->file_name . '.gif';
        @copy(PHPWS_SOURCE_DIR . 'mod/filecabinet/inc/embed/flickr_ss/flickr_logo.gif');
        $media->thumbnail = $thumb_name;
    }

    $media->width = 400;
    $media->height = 400;

    return true;
}


?>