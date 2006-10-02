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

    case version_compare($version, '0.1.6', '<'):
        $files = 'config/error.php';
        PHPWS_Boost::updateFiles($files, 'filecabinet');
        $error = false;
        $type = PHPWS_DB::getDBType();
        if ($type == 'mysql') {
            $sql = 'ALTER TABLE documents MODIFY description text null';
        } else {
            $sql = 'ALTER TABLE documents ALTER COLUMN description drop NOT NULL';
        }

        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            $error = true;
            PHPWS_Error::log($result);
        }

        if ($error) {
            $content[] = 'Failed converting documents table\'s description columns.';
            return false;
        } else {
            $content[] = 'Fix - Changed description column in documents table to allow null values.';
            $content[] = 'Fix - Renamed define variable.';
        }

    }

    return true;
}


?>