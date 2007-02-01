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

    case version_compare($version, '0.1.7', '<'):
        $files = array();
        $files[] = 'javascript/post_file/body.js';
        PHPWS_Boost::updateFiles($files, 'filecabinet');
        $content[] = 'Fix - Image manager window not updating parent hidden value.';

    case version_compare($version, '0.2.0', '<'):
        $files = array();
        $files[] = 'javascript/clear_image/head.js';
        $files[] = 'javascript/post_file/body.js';
        $files[] = 'templates/style.css';
        $files[] = 'templates/cookie_directory.tpl';
        $files[] = 'templates/manager/pick.tpl';
        $files[] = 'conf/error.php';

        PHPWS_Boost::updateFiles($files, 'filecabinet');
        $type = PHPWS_DB::getDBType();
        if ($type == 'mysql') {
            $sql = 'ALTER TABLE images MODIFY file_name varchar(255) NOT NULL';
        } else {
            $sql = 'ALTER TABLE images ALTER COLUMN file_name varchar(255) NOT NULL';
        }
        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            $error = true;
            PHPWS_Error::log($result);
        }
        $content[] = '<pre>
+ Increased file name size.
+ Fixed javascript errors with clear image link.
+ Added image directory selection in Image Manager
+ Root document directory should work now.
</pre>';

    }

    return true;
}


?>