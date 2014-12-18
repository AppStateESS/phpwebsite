<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


if ($is_video) {
        $tpl['THUMBNAIL'] = $thumbnail;
} else {
    $tpl['HEIGHT'] = 20;
    $tpl['WIDTH'] = 260;
}
?>