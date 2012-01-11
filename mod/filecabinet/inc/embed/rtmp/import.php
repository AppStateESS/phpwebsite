<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

function rtmp_import($media)
{
    $file_path = $media->file_name;
    if (!preg_match('/^rtmp:/i', $file_path)) {
        return false;
    }

    $colon = stripos($file_path, ':', 6);
    if ($colon) {
        if (substr($file_path, $colon - 3, 3) == 'mp4') {
            $breakpoint = $colon - 3;
        } else {
            $breakpoint = $colon;
        }
        $file_directory = substr($file_path, 0, $breakpoint);
        $file_name = substr($file_path, $breakpoint);
    } else {
        $last_slash = strrpos($file_path, '/');
        $file_name = substr($file_path, $last_slash + 1);
        $file_directory = str_replace($file_name, '', $file_path);
    }

    $media->file_name = $file_name;
    $media->file_directory = $file_directory;
    $media->width = 400;
    $media->height = 300;
    $media->thumbnail = PHPWS_SOURCE_HTTP . 'mod/filecabinet/img/video_generic.jpg';
    $media->title = 'Posted ' . strftime('%Y/%m/%d');
    return true;
}

?>
