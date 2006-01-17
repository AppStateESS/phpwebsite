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
     * return array of directories
     * @author Matthew McNaney <mcnaney at gmail dot com>
     */
    function listDirectories($root_dir)
    {
        if (!is_dir($root_dir)) {
            return FALSE;
        }

        $listing = scandir($root_dir);

        if (empty($listing)) {
            return FALSE;
        }

        foreach ($listing as $directory) {
            if ($directory == '.' || $directory == '..' || !is_dir($root_dir . $directory)) {
                continue;
            }

            $directories[] = $directory;
        }
        return $directories;
    }


    function readDirectory($path, $directoriesOnly=FALSE, $filesOnly=FALSE, $recursive=FALSE, $extensions=array(), $appendPath=FALSE)
    {
        if($directoriesOnly && $filesOnly) {
            $directoriesOnly = FALSE;
            $filesOnly = FALSE;
        }

        if (!is_dir($path)) {
            return FALSE;
        }

        $dir = dir($path);
        while ($file = $dir->read()){
            $fullpath = $path . $file;
            if ($directoriesOnly && !$filesOnly && @is_dir($fullpath) && !preg_match('/~$/', $file) && $file != '.' && $file != '..' && $file != 'CVS') {
                if($appendPath) {
                    $directory[] = $fullpath;
                } else {
                    $directory[] = $file;
                }
            } elseif (!$directoriesOnly && $filesOnly && !is_dir($path . $file) && !preg_match('/~$/', $file) && $file != 'CVS' && $file != '.' && $file != '..') {
                if (is_array($extensions) && count($extensions) > 0) {
                    $extTest = explode('.', $file);
                    if (in_array($extTest[1], $extensions)) {
                        if ($appendPath) {
                            $directory[] = $fullpath;
                        } else {
                            $directory[] = $file;
                        }
                    }
                } elseif ($appendPath) {
                    $directory[] = $fullpath;
                } else {
                    $directory[] = $file;
                }
            } elseif (!$directoriesOnly && !$filesOnly && $file != '.' && $file != '..' && $file != 'CVS') {
                if (!is_dir($path . $file) && is_array($extensions) && count($extensions) > 0) {
                    $extTest = explode('.', $file);
                    if (in_array($extTest[1], $extensions)) {
                        if ($appendPath) {
                            $directory[] = $fullpath;
                        } else {
                            $directory[] = $file;
                        }
                    } elseif ($appendPath) {
                        $directory[] = $fullpath;
                    } else {
                        $directory[] = $file;
                    }
                } else {
                    if ($appendPath) {
                        $directory[] = $fullpath;
                    } else {
                        $directory[] = $file;
                    }
                }
            }

            if ($recursive && is_dir($fullpath) && $file != 'CVS' && $file != '.' && $file != '..') {
                $directory = array_merge($directory, PHPWS_File::readDirectory($fullpath . '/', $directoriesOnly, $filesOnly, $recursive, $extensions, $appendPath));
            }
        }
        $dir->close();

        if (isset($directory)) {
            return $directory;
        } else {
            return NULL;
        }
    }// END FUNC readDirectory()

    /**
     * Recursively copies files from one directory to another.
     *
     * @author Matthew McNaney <mcnaney at gmail dot com>
     */
    function copy_directory($source_directory, $dest_directory) {
        if (preg_match('/\/?/', $source_directory)) {
            $source_directory .= '/';
        }

        if (preg_match('/\/?/', $dest_directory)) {
            $dest_directory .= '/';
        }

        if (!is_dir($dest_directory)) {
            if(!@mkdir($dest_directory)) {
                PHPWS_Error::log(PHPWS_DIR_CANT_CREATE, 'core', 'PHPWS_File::recursiveFileCopy', $dest_directory);
                return FALSE;
            }
        }

        $source_files = scandir($source_directory);
        if (empty($source_files)) {
            return TRUE;
        }

        foreach ($source_files as $file_name) {
            if ($file_name == '.' || $file_name == '..' || $file_name == 'CVS') {
                continue;
            }

            if (is_file($source_directory . $file_name)) {
                if (!is_writable($dest_directory)) {
                    PHPWS_Error::log(PHPWS_DIR_NOT_WRITABLE, 'core', 'PHPWS_File::recursiveFileCopy', $toPath);
                    return FALSE;
                }
                if (!@copy($source_directory . $file_name, $dest_directory . $file_name)) {
                    return FALSE;
                }
            }  elseif (is_dir($source_directory . $file_name)) {
                if(!PHPWS_File::copy_directory($source_directory . $file_name . '/', $dest_directory . $file_name . '/')) {
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



    /**
     * Creates a thumbnail of a jpeg or png image.
     *
     * @author   Jeremy Agee
     * @modified Adam Morton
     * @modified Steven Levin
     * @modified Matthew McNaney <mcnaney at gmail dot com>
     * @param    string  $fileName          The file name of the image you want thumbnailed.
     * @param    string  $directory         Path to the file you want thumbnailed
     * @param    string  $tndirectory       The path to where the new thumbnail file is stored
     * @param    integer $maxHeight         Set width of the thumbnail if you do not want to use the default 
     * @param    integer $maxWidth          Set height of the thumbnail if you do not want to use the default
     * @return   array   0=>thumbnailFileName, 1=>thumbnailWidth, 2=>thumbnailHeight 
     * @access   public
     */
    function makeThumbnail($fileName, $directory, $tndirectory, $maxWidth=125, $maxHeight=125)
    {
        $image = $directory . $fileName;
        $imageInfo = getimagesize($image);

        if(($imageInfo[0] < $maxWidth) && ($imageInfo[1] < $maxHeight)) {
            return array($fileName, $imageInfo[0], $imageInfo[1]);
        } else {
            if($imageInfo[0] > $imageInfo[1]) {
                $scale = $maxWidth / $imageInfo[0];
            } else{
                $scale = $maxHeight / $imageInfo[1];
            }
        }

        $thumbnailWidth = round($scale * $imageInfo[0]);
        $thumbnailHeight = round($scale * $imageInfo[1]);
        $thumbnailImage = NULL;
        if(PHPWS_File::chkgd2()) {
            $thumbnailImage = ImageCreateTrueColor($thumbnailWidth, $thumbnailHeight);
            imageAlphaBlending($thumbnailImage, false);
            imageSaveAlpha($thumbnailImage, true);
        } else {
            $thumbnailImage = ImageCreate($thumbnailWidth, $thumbnailHeight);
        }
  
        if ($imageInfo[2] == 1) {
            $fullImage = imagecreatefromgif($image);
        } elseif ($imageInfo[2] == 2) {
            $fullImage = imagecreatefromjpeg($image);
        } elseif ($imageInfo[2] == 3) {
            $fullImage = imagecreatefrompng($image);
        }
  
        ImageCopyResized($thumbnailImage, $fullImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, ImageSX($fullImage), ImageSY($fullImage));
        ImageDestroy($fullImage);

        $thumbnailFileName = explode('.', $fileName);

        if ($imageInfo[2] == 1) {
            $thumbnailFileName = $thumbnailFileName[0] . '_tn.gif';
            imagegif($thumbnailImage, $tndirectory . $thumbnailFileName);
        } elseif ($imageInfo[2] == 2) {
            $thumbnailFileName = $thumbnailFileName[0] . '_tn.jpg';
            imagejpeg($thumbnailImage, $tndirectory . $thumbnailFileName);
        } elseif ($imageInfo[2] == 3) {
            $thumbnailFileName = $thumbnailFileName[0] . '_tn.png';
            imagepng($thumbnailImage, $tndirectory . $thumbnailFileName);
        }

        return array($thumbnailFileName, $thumbnailWidth, $thumbnailHeight);
    } // END FUNC makeThumbnail()

    function rmdir($dir) 
    {
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
     * @param    int    $maxlen Maximun permited string lenght
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

}
?>