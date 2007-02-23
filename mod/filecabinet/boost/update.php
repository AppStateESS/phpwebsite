<?php
  /**
   * @author Matthew McNaney
   * @version $Id$
   */

function filecabinet_update(&$content, $version)
{

    switch ($version) {
    case version_compare($version, '0.1.7', '<'):
        $content[] = 'This package will not update versions under 0.1.7';
        return false;

    case version_compare($version, '0.3.1', '<'):
        $type = PHPWS_DB::getDBType();
        if ($type == 'mysql') {
            $sql = 'ALTER TABLE images MODIFY file_name varchar(255) NOT NULL';
        } else {
            $sql = 'ALTER TABLE images ALTER COLUMN file_name TYPE varchar(255)';
        }

        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            $content[] = 'Failed increasing images.file_name column';
            PHPWS_Error::log($result);
            return false;
        }

        $files = array();
        $files[] = 'javascript/clear_image/head.js';
        $files[] = 'javascript/post_file/body.js';
        $files[] = 'templates/style.css';
        $files[] = 'templates/cookie_directory.tpl';
        $files[] = 'templates/manager/pick.tpl';
        $files[] = 'conf/error.php';
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = 'The following files updated successfully:';
        } else {
            $content[] = 'The following files failed to update successfully:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
0.3.1 changes
-------------
+ Removed references from object constructors
+ Added missing comment lines
+ Added translate statements
+ Added system upload memory check. Overrides site setting in form.
+ Fixed base directory uploads
+ Document was changing the object id to null when failing to load the
  object. Changed it to a zero.
+ Added more directory error checks.
+ Image manager will try and match the current module to its directory.
+ New error message for bad directory choice
+ Increased file name size in database.
+ Added image directory selection to pick image menu
+ Choosing an image directory only shows images from that directory
+ Removed choice of image directory root. Will always be images/
+ Fixed root document directory. Now actually puts files in said
  directory.
+ Upload windows choose the default directory better
+ Removed [default] tag from directory listing
+ Fixed bug in image manager. Was ignoring width and height upload
  restrictions.
+ Lowercased bools
+ Changed \'x\' to \'by\' in error message.
';

    case version_compare($version, '0.3.2', '<'):
        $files = array('img/nogd.png');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = '+ nogd.png image copied successfully.';
        } else {
            $content[] = '! nogd.png failed to copy to images/mod/filecabinet/';
        }

        $content[] = '
0.3.2 changes
-------------
+ Removed test function call.
+ Added "loadDimensions" function to image class
+ Added a gd lib check to image manager. Uses the nogd.png image for a
  thumbnail if fails.
</pre>';
    }

    return true;
}


?>