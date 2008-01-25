<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$floatbar       = false;
$use_thumbnails = true;

if ($is_video) {
    if ($floatbar) {
        $tpl['DISPLAYHEIGHT'] = $tpl['HEIGHT'];
    } else {
        $tpl['HEIGHT'] = $tpl['HEIGHT'] + 20;
    }

    if ($use_thumbnails) {
        $tpl['THUMBNAIL'] = $thumbnail;
    }
} else {
    $tpl['HEIGHT'] = 20;
    $tpl['WIDTH'] = 260;
}




?>