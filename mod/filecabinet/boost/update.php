<?php
  /**
   * @author Matthew McNaney
   * @version $Id$
   */

function filecabinet_update(&$content, $version)
{

    switch ($version) {
    case version_compare($version, '0.1.5', '<'):
        $content[] = 'Fixed unclipping after clipping an image.';
        $content[] = 'Image manager upload will try and match 
the default directory to the module that is accessing it.';
        $files[] = 'javascript/clear_image/head.js';
        $files[] = 'javascript/post_file/body.js';
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = 'Files copied locally:';
            $content[] = implode('<br />', $files);
        } else {
            $content[] = 'Failed to copy javascript files locally.';
            return false;
        }
    }

    return true;
}


?>