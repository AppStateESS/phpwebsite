<?php

  /**
   * Returns the contents of a directory in an array
   * 
   * If directoriesOnly is TRUE, then only directories will be listed.
   * If filesOnly is TRUE, then only files will be listed.
   * Function returns directory names and file names by default.
   * Special directories '.', '..', and 'CVS' are not returned.
   *
   * @author                            Matt McNaney <matt@NOSPAM.tux.appstate.edu>
   * @modified                          Adam Morton <adam@NOSPAM.tux.appstate.edu>
   * @param    string   path            The path to directory to be read
   * @param    boolean  directoriesOnly If TRUE, return directory names only
   * @param    boolean  filesOnly       If TRUE, return file names only
   * @param    boolean  recursive       If TRUE, readDirectory will recurse through the given directory and all directories 'beneath' it.
   * @param    array    extensions      An array containing file extensions of files you wish to have returned.
   * @param    boolean  appendPath      Whether or not to append the full path to all entries returned
   * @return   array    directory       An array containing the names of directories and/or files in the specified directory.
   * @access   public
   */


class PHPWS_File {
    /**
     * Returns an array of directories
     * 
     * Is called recursively is recursive is TRUE.
     *
     * @param boolean with_root If true, return results prefixed with the root dir
     * @author Matthew McNaney <mcnaney at gmail dot com>
     * @return array directories Array of directories if successful, NULL if nothing found
     */
    function listDirectories($root_dir, $with_root=FALSE, $recursive=FALSE)
    {
        $directories = NULL;

        PHPWS_File::appendSlash($root_dir);

        if (!is_dir($root_dir) || !is_readable($root_dir)) {
            return FALSE;
        }

        $listing = scandir($root_dir);

        if (empty($listing)) {
            return FALSE;
        }

        foreach ($listing as $directory) {
            $full_dir = $root_dir . $directory;
            if (strpos($directory, '.') === 0 || !is_dir($full_dir)) {
                continue;
            }

            if ($with_root) {
                $directories[] = $full_dir;
            } else {
                $directories[] = $directory;
            }

            if ($with_root && $recursive) {
                $subdir = PHPWS_File::listDirectories($full_dir,TRUE,TRUE);
                if ($with_root) {
                    if (!empty($subdir)) {
                        $directories = array_merge($directories, $subdir);
                    }
                }
            }
        }

        return $directories;
    }


    /**
     * Cannot set files_only and recursive to true
     */
    function readDirectory($path, $directories_only=false, $files_only=false, $recursive=false, $extensions=null)
    {
        static $first_path = null;
        $listing = null;

        PHPWS_File::appendSlash($path);

        if (empty($first_path)) {
            $first_path = $path;
        }

        if ($directories_only && $files_only) {
            $directories_only = $files_only = false;
        }

        if (!is_dir($path)) {
            return false;
        }

        $dir = dir($path);

        while($file = $dir->read()) {
            $fullpath = $path . $file;
            $showpath = str_replace($first_path, '', $fullpath);

            if (strpos($file, '.') === 0) {
                // skips hidden files, directories and back references
                continue;
            }

            if (is_dir($fullpath)) {
                if ($files_only) {
                    continue;
                }

                if (empty($extensions)) {
                    $listing[] = $showpath;
                }

                if ($recursive) {
                    $subdir = PHPWS_File::readDirectory($fullpath, $directories_only, false, true, $extensions);

                    if (!empty($subdir)) {
                        if (!empty($listing)) {
                            $listing = array_merge($listing, $subdir);
                        } else {
                            $listing = $subdir;
                        }
                    }
                }
            } elseif ($directories_only) {
                continue;
            } else {
                if (!empty($extensions)) {
                    $aFile = explode('.', $file);
                    $ext = array_pop($aFile);

                    if (!in_array($ext, $extensions)) {
                        continue;
                    }
                }
                $listing[] = $showpath;
            }
        }
        $dir->close();
        return $listing;
    }



    /**
     * Recursively copies files from one directory to another.
     *
     * @author Matthew McNaney <mcnaney at gmail dot com>
     */
    function copy_directory($source_directory, $dest_directory) {
        PHPWS_File::appendSlash($source_directory);
        PHPWS_File::appendSlash($dest_directory);

        if (!is_dir($dest_directory)) {
            if(!@mkdir($dest_directory)) {
                PHPWS_Error::log(PHPWS_DIR_CANT_CREATE, 'core', 'PHPWS_File::recursiveFileCopy', $dest_directory);
                return FALSE;
            }
            @chmod($dest_directory, 0755);
        }

        if (!is_writable($dest_directory)) {
            PHPWS_Error::log(PHPWS_DIR_NOT_WRITABLE, 'core', 'PHPWS_File::recursiveFileCopy', $dest_directory);
            return FALSE;
        }

        $source_files = scandir($source_directory);
        if (empty($source_files)) {
            return TRUE;
        }

        foreach ($source_files as $file_name) {
            // ignore directories, cvs, and backups
            if ($file_name == '.' || $file_name == '..' || $file_name == 'CVS'
                || preg_match('/~$/', $file_name)) {
                continue;
            }

            $dest_file = $dest_directory . $file_name;
            if (is_file($source_directory . $file_name)) {
                if (!@copy($source_directory . $file_name, $dest_file)) {
                    if (!is_writable($dest_file)) {
                        PHPWS_Error::log(PHPWS_FILE_NOT_WRITABLE, 'core', 'PHPWS_File::recursiveFileCopy', $dest_file);
                    } else {
                        PHPWS_Error::log(PHPWS_FILE_NO_COPY, 'core', 'PHPWS_File::recursiveFileCopy', $dest_file);
                    }
                }
            }  elseif (is_dir($source_directory . $file_name)) {
                if(!PHPWS_File::copy_directory($source_directory . $file_name . '/', $dest_file . '/')) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    function recursiveFileCopy($source_dir, $dest_dir) {
        return PHPWS_File::copy_directory($source_dir, $dest_dir);
    }

    function writeFile($filename, $text, $allowOverwrite=FALSE, $errorReport=FALSE)
    {
        if (!$allowOverwrite) {
            if (@is_writable($filename)) {
                return FALSE;
            }
        }

        return file_put_contents($filename, $text);
    }// END FUNC writeFile()


    function readFile($filename, $error=NULL)
    {
        return file_get_contents($filename);
    }

    /**
     * Copies a file from one directory to another
     *
     * This function comes from php.net by jacob@keystreams.com.
     *
     * Example Usage:
     * $copy = fileCopy('/path/to/original.file', '/path/to/', 'destination.file', 1, 1);
     *
     * @author   jacob@NOSPAM.keystreams.com <jacob@NOSPAM.keystreams.com>
     * @modified Adam Morton <adam@NOSPAM.tux.appstate.edu>
     * @param    string  $file_origin           Path to file to be copied
     * @param    string  $destination_directory Directory to copy to
     * @param    string  $file_destination      Name to be given to copied file
     * @param    boolean $overwrite             If TRUE overwrite any file in the destination directory
     * @param    boolean $fatal                 If TRUE echo an error if the file does not exist.
     * @return   boolean TRUE on success, FALSE on failure
     * @access   public
     */
    function fileCopy($file_origin, $destination_directory, $file_destination, $overwrite, $fatal) 
    {
        if ($fatal) {
            $fp = @fopen($file_origin, 'rb');

            if (!$fp) {
                return PHPWS_Error::get(PHPWS_FILE_CANT_READ, 'core', 'PHPWS_File::fileCopy', $file_origin);
            }
      
            $dir_check = @is_writable($destination_directory);
            if (!$dir_check) {
                return PHPWS_Error::get(PHPWS_DIR_NOT_WRITABLE, 'core', 'PHPWS_File::fileCopy', $destination_directory);
            }
      
            $dest_file_exists = file_exists($destination_directory . $file_destination);
      
            if ($dest_file_exists) { 
                if ($overwrite) {
                    $fp = @is_writable($destination_directory . $file_destination);
                    if (!$fp) {
                        return PHPWS_Error::get(PHPWS_DIR_NOT_WRITABLE, 'core', 'PHPWS_File::fileCopy', $destination_directory);
                    }

                    if ($copy_file = @copy($file_origin, $destination_directory . $file_destination)) {
                        return TRUE;
                    } else {
                        return FALSE;
                    }
                }                                       
            } else {
                if ($copy_file = @copy($file_origin, $destination_directory . $file_destination)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        } else {
            if($copy_file = @copy($file_origin, $destination_directory . $file_destination))
                return TRUE;
            else
                return FALSE;
        }
    }// END FUNC fileCopy()

    /**
     * Makes a new directory given a path name
     *
     * @author   Darren Greene <dg49379@NOSPAM.tux.appstate.edu>
     * @param    string  $pathname     name of the path to create directory
     * @param    string  $permissions  octal Unix Permissions
     * @return   boolean $dirCreated   true if directory was created
     * @access   public
     */
    function makeDir($pathname, $permissions=NULL) 
    {
        if(is_dir($pathname)) {
            return true;
        }

        $dirCreated = false;
        $oldMask = umask();
        
        if ($permissions != NULL) {
            $dirCreated = @mkdir($pathname, $permissions);
        } else {
            $dirCreated = @mkdir($pathname, PHPWS_DIR_PERMISSIONS);
        }
        
        umask($oldMask);
        
        return $dirCreated;
    }


    function imageCopy($path, $file_type)
    {
        if ($file_type == 'image/gif') {
            return imagecreatefromgif($path);
        } elseif ( $file_type == 'image/jpeg' || $file_type == 'image/pjpeg' ||
                   $file_type == 'image/jpg' ) {
            return imagecreatefromjpeg($path);
        } elseif ( $file_type == 'image/png' || $file_type == 'image/x-png' ) {
            return imagecreatefrompng($path);
        } else {
            return false;
        }
    }

    function rotateImage($source_dir, $dest_dir, $degrees)
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $size = getimagesize($source_dir);
        if (empty($size)) {
            return false;
        }

        $width     = & $size[0];
        $height    = & $size[1];
        $file_type = & $size['mime'];


        if ($degrees > 360) {
            $degrees = $degrees % 360;
        }

        $source = PHPWS_File::imageCopy($source_dir, $file_type);
        $rotate = imagerotate($source, $degrees, 0);

        if ($file_type == 'image/png'|| $file_type == 'image/x-png' ) {
            $result = imagepng($rotate, $dest_dir);
        } elseif ($file_type == 'image/gif') {
            $result = imagegif($rotate, $dest_dir);
        } elseif ( $file_type == 'image/jpeg' || $file_type == 'image/pjpeg' ||
                   $file_type == 'image/jpg' ) {
            $result = imagejpeg($rotate, $dest_dir);
        } else {
            return FALSE;
        }

        imagedestroy($rotate);        
        if ($result) {
            chmod($dest_dir, 0644);
        }

        return $result;
    }

    /**
     * This is a modified version of the script written by feip at feip dot net.
     * It was copied from php.net at:
     * http://www.php.net/manual/en/function.imagecopyresized.php
     */
    function resizeImage($source_dir, $dest_dir, $new_width, $new_height, $force_png=false) {
        if (empty($new_width) || empty($new_height)) {
            return false;
        }

        if (!extension_loaded('gd')) {
            if (!dl('gd.so')) {
                @copy(PHPWS_HOME_DIR . 'images/mod/filecabinet/nogd.png', $dest_dir);
                return true;
            }
        }

        $size = getimagesize($source_dir);
        if (empty($size)) {
            return false;
        }

        $width     = & $size[0];
        $height    = & $size[1];
        $file_type = & $size['mime'];

        if ( ($width < $new_width) &&
             ($height < $new_height) ) {
            return @copy($source_dir, $dest_dir);
        }

        $source_image = PHPWS_File::imageCopy($source_dir, $file_type);

        $proportion_X = $width / $new_width;
        $proportion_Y = $height / $new_height;

        if($proportion_X > $proportion_Y ) {
            $proportion = $proportion_Y;
            $pure = $proportion_X / $proportion_Y;
        } else {
            $proportion = $proportion_X ;
            $pure = $proportion_Y / $proportion_X;
        }

        $target['width'] = $new_width * $proportion;
        $target['height'] = $new_height * $proportion;

        $original['diagonal_center'] = round( sqrt( ($width*$width) + ($height*$height) ) / 2);
        $target['diagonal_center'] = round( sqrt( ($target['width']*$target['width']) + ($target['height']*$target['height']) ) / 2);

        $crop = round($original['diagonal_center'] - $target['diagonal_center']);

        if ($width < $new_width && $height >= $new_height ||
            $height < $new_height && $width >= $new_width) {
            $target['y'] = $target['x'] = 0;
        } else if($proportion_X < $proportion_Y ) {
            $target['x'] = 0;
            $target['y'] = round((($height/2)*$crop)/$original['diagonal_center']);
        } else {
            $target['x'] =  round((($width/2)*$crop)/$original['diagonal_center']);
            $target['y'] = 0;
        }

        if(PHPWS_File::chkgd2()) {
            $resampled_image = imagecreatetruecolor($new_width, $new_height);
            imagealphablending($resampled_image, false);
            imagesavealpha($resampled_image, true);
        } else {
            $resampled_image = imagecreate($new_width, $new_height);
        }

        $destination_x = 0;
        $destination_y = 0;

        imagecopyresampled($resampled_image,  $source_image,  $destination_x, $destination_y, $target['x'],
                            $target['y'], $new_width, $new_height, $target['width'], $target['height']);

        imagedestroy($source_image);

        if ( $force_png || $file_type == 'image/png'|| $file_type == 'image/x-png' ) {
            $result = imagepng($resampled_image, $dest_dir);
        } elseif ($file_type == 'image/gif') {
            $result = imagegif($resampled_image, $dest_dir);
        } elseif ( $file_type == 'image/jpeg' || $file_type == 'image/pjpeg' ||
                   $file_type == 'image/jpg' ) {
            $result = imagejpeg($resampled_image, $dest_dir);
        } else {
            return FALSE;
        }
        if ($result) {
            chmod($dest_dir, 0644);
        }
        imagedestroy($resampled_image);
        return $result;
    }

    /**
     * Creates a thumbnail of a jpeg, gif or png image.  (Gif images are converted to
     * jpeg thumbnails due to licensing issues.)  The thumbnail file is created as
     * a separate "_tn" file or, if desired, as a replacement for the original.
     *
     * @author   Jeremy Agee <jagee@NOSPAM.tux.appstate.edu>
     * @modified Adam Morton <adam@NOSPAM.tux.appstate.edu>
     * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
     * @modified George Brackett <gbrackett@NOSPAM.luceatlux.com>
     * @modified Matt McNaney <matt at tux dot appstate dot edu>
     * @param    string  $fileName          The file name of the image you want thumbnailed.
     * @param    string  $directory         Path to the file you want thumbnailed
     * @param    string  $tndirectory       The path to where the new thumbnail file is stored
     * @param    integer $maxHeight         Set width of the thumbnail if you do not want to use the default 
     * @param    integer $maxWidth          Set height of the thumbnail if you do not want to use the default
     * @param        boolean $replaceFile           Set TRUE if thumbnail should replace original file
     * @return   array   0=>thumbnailFileName, 1=>thumbnailWidth, 2=>thumbnailHeight 
     * @access   public
     */
    function makeThumbnail($fileName, $directory, $tndirectory, $maxWidth=125, $maxHeight=125, $replaceFile=FALSE) {
        $image = $directory . $fileName;
        $imageInfo = getimagesize($image);

        // Check to make sure gd will support the specified type
        $supported = FALSE;
        // Index 2 is a flag indicating the type of the image: 1 = GIF, 2 = JPG, 3 = PNG, 
        // 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 
        // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM.
        switch($imageInfo[2]) {
        case 1:         // we're converting GIF to JPG
        case 2:
            if(imagetypes() & IMG_JPG)
                $supported = TRUE;
            break;
        case 3:
            if(imagetypes() & IMG_PNG)
                $supported = TRUE;
            break;
        }
    
        if (!$supported) {
            return PHPWS_Error::get(PHPWS_GD_ERROR, 'core', 'PHPWS_File::makeThumbnail', $imageInfo['mime']);
        }

        $currentWidth = &$imageInfo[0];
        $currentHeight = &$imageInfo[1];

        if(($currentWidth < $maxWidth) && ($currentHeight < $maxHeight)) {
            return array($fileName, $currentWidth, $currentHeight);
        } else {
            $widthScale  = $maxWidth / $currentWidth;
            $heightScale = $maxHeight / $currentHeight;

            $adjusted_h_to_w = floor($currentHeight * $widthScale);
            $adjusted_w_to_w = floor($currentWidth * $widthScale);

            if ( ($adjusted_h_to_w <= $maxHeight) &&
                 ($adjusted_w_to_w <= $maxWidth)) {
                $finalScale = $widthScale;
            } else {
                $finalScale = $heightScale;
            }
        }

        $thumbnailWidth = round($finalScale * $currentWidth);
        $thumbnailHeight = round($finalScale * $currentHeight);
        $thumbnailImage = NULL;
      
        // create image space in memory
        if(PHPWS_File::chkgd2()) {
            $thumbnailImage = ImageCreateTrueColor($thumbnailWidth, $thumbnailHeight);
            imageAlphaBlending($thumbnailImage, false);
            imageSaveAlpha($thumbnailImage, true);
        } else {
            $thumbnailImage = ImageCreate($thumbnailWidth, $thumbnailHeight);
        }
        // now pull in image data
        switch($imageInfo[2]) {
        case 1:
            $fullImage = ImageCreateFromGIF($image);
            break;
        case 2:
            $fullImage = ImageCreateFromJPEG($image);
            break;
        case 3:
            $fullImage = ImageCreateFromPNG($image);
        }
    
        // now create the thumbnail image in memory
        if(PHPWS_File::chkgd2()) {
            ImageCopyResampled($thumbnailImage, $fullImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, ImageSX($fullImage), ImageSY($fullImage));
        } else {
            ImageCopyResized($thumbnailImage, $fullImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, ImageSX($fullImage), ImageSY($fullImage));
        }
    
        ImageDestroy($fullImage);
        $thumbnailFileName = explode('.', $fileName);
    
        if($replaceFile) {
            unlink($image);
        }
    
        switch($imageInfo[2]) {
        case 1:         // convert gif to jpg
        case 2:
            $thumbnailFileName = $thumbnailFileName[0] . ($replaceFile ? '.jpg' : '_tn.jpg');
            imagejpeg($thumbnailImage, $tndirectory . $thumbnailFileName);
            break;
        case 3:
            $thumbnailFileName = $thumbnailFileName[0] . ($replaceFile ? '.png' : '_tn.png');
            imagepng($thumbnailImage, $tndirectory . $thumbnailFileName);
            break;
        }

        return array($thumbnailFileName, $thumbnailWidth, $thumbnailHeight);
    
    } // END FUNC makeThumbnail()

    function rmdir($dir) 
    {
        PHPWS_File::appendSlash($dir);

        if(is_dir($dir)) {
            $handle = opendir($dir);
            while($file = readdir($handle)) {
                if($file == '.' || $file == '..') {
                    continue;
                } elseif(is_dir($dir . $file)) {
                    PHPWS_File::rmdir($dir . $file);
                } elseif(is_file($dir . $file)) {
                    $result = @unlink($dir . $file);
                    if (!$result) {
                        PHPWS_Error::log(PHPWS_FILE_DELETE_DENIED, 'core', 'PHPWS_File::rmdir', $dir . $file);
                        return FALSE;
                    }
                }
            }
            closedir($handle);

            $result = @rmdir($dir);
            if (!$result) {
                PHPWS_Error::log(PHPWS_DIR_DELETE_DENIED, 'core', 'PHPWS_File::rmdir', $dir);
                return FALSE;
            }

            return TRUE;
        } else {
            return FALSE;
        }
    }// END FUNC rmdir()


    function chkgd2()
    {
        if(function_exists('gd_info')) {
            $gdver = gd_info();
            if(strpos($gdver['GD Version'], '1.') != FALSE) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            ob_start();
            phpinfo(8);
            $phpinfo=ob_get_contents();
            ob_end_clean();
            $phpinfo=strip_tags($phpinfo);
            $phpinfo=stristr($phpinfo,'gd version');
            $phpinfo=stristr($phpinfo,'version');
            $end=strpos($phpinfo,' ');
            $phpinfo=substr($phpinfo,0,$end);
            $phpinfo=substr($phpinfo,7);
            if(version_compare('2.0', $phpinfo) == 1) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
    }// END FUNC chkgd2()

    /**
     * Format a file name to be safe
     * Based on pear.php.net HTTP_Upload_File::nameToSafe()
     *
     * @modified Shaun Murray <shaun@NOSPAM.aegisdesign.co.uk>
     * @param    string $file   The string file name
     * @param    int    $maxlen Maximun permited string length
     * @return   string Formatted file name
     */
    function nameToSafe($name, $maxlen=250)
    {
        $noalpha = 'ÁÉÍÓÚÝáéíóúýÂÊÎÔÛâêîôûÀÈÌÒÙàèìòùÄËÏÖÜäëïöüÿÃãÕõÅåÑñÇç@°ºª';
        $alpha   = 'AEIOUYaeiouyAEIOUaeiouAEIOUaeiouAEIOUaeiouyAaOoAaNnCcaooa';

        $name = substr($name, 0, $maxlen);
        $name = strtr($name, $noalpha, $alpha);
        // not permitted chars are replaced with "_"
        return preg_replace('/[^\w\.]/i', '_', $name);
    }


    function getFileExtension($filename)
    {
        $last_dot = strrpos($filename, '.') + 1;
        $ext = strlen($filename) - $last_dot;
        return substr($filename, $last_dot, $ext);
    }

    function appendSlash(&$directory)
    {
        if (!preg_match('/\/$/', $directory)) {
            $directory .= '/';
        }
    }
}


/**
 * This function was written by Thomaschaaf
 * and copied from php.net
 * http://us.php.net/manual/en/function.imagerotate.php
 */
if (!function_exists('imagerotate')) {
    function imagerotate($image, $degrees) {
        
        $src_x = imagesx($image);
        $src_y = imagesy($image);
        if ($degrees == 180) {
            $dest_x = $src_x;
            $dest_y = $src_y;
        } else {
            $dest_x = $src_y;
            $dest_y = $src_x;
        }
        /*
        elseif ($src_x <= $src_y) {
            $dest_x = $src_y;
            $dest_y = $src_x;
        }
        elseif ($src_x >= $src_y) {
            $dest_x = $src_y;
            $dest_y = $src_x;
        }
        */

        $rotate=imagecreatetruecolor($dest_x,$dest_y);
        imagealphablending($rotate, false);
               
        switch ($degrees) {
        case 270:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $dest_x - $y - 1, $x, $color);
                }
            }
            break;
        case 90:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $y, $dest_y - $x - 1, $color);
                }
            }
            break;
        case 180:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $dest_x - $x - 1, $dest_y - $y - 1, $color);
                }
            }
            break;
        default:
            $rotate = $image;
        }
        return $rotate;
    }
}



?>
