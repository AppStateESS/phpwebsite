<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$video_types = array(
                     'flv'  => 'video/x-flv',
                     'flv1' => 'application/x-extension-flv',
                     'flv2' => 'application/x-flash-video',
                     'swf'   => 'application/x-shockwave-flash',
                     'avi'  => 'video/x-msvideo',
                     'mov'  => 'video/quicktime',
                     'mpeg' => 'video/mpeg',
                     'mpg'  => 'video/mpeg',
                     'mpe'  => 'video/mpeg',
                     'asf'  => 'video/x-ms-asf',
                     'asx'  => 'video/x-ms-asf',
                     'wvx'  => 'video/x-ms-wvx',
                     'wm'   => 'video/x-ms-wm',
                     'wmx'  => 'video/x-ms-wmx',
                     'wmv'  => 'video/x-ms-wmv',
                     'wmz'  => 'application/x-ms-wmz',
                     'wmd'  => 'application/x-ms-wmd'
                     );
define('FC_VIDEO_TYPES', serialize($video_types));
?>