<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class PHPWS_File {
	/**
	 * Returns an array of directories
	 *
	 * Is called recursively is recursive is true.
	 *
	 * @param boolean with_root If true, return results prefixed with the root dir
	 * @author Matthew McNaney <mcnaney at gmail dot com>
	 * @return array directories Array of directories if successful, NULL if nothing found
	 */
	public function listDirectories($root_dir, $with_root=false, $recursive=false)
	{
		$directories = NULL;

		PHPWS_File::appendSlash($root_dir);

		if (!is_dir($root_dir) || !is_readable($root_dir)) {
			return false;
		}

		$listing = scandir($root_dir);

		if (empty($listing)) {
			return false;
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
				$subdir = PHPWS_File::listDirectories($full_dir,true,true);
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
	public static function readDirectory($path, $directories_only=false, $files_only=false, $recursive=false, $extensions=null, $indeep=false)
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
					$subdir = PHPWS_File::readDirectory($fullpath, $directories_only, false, true, $extensions, true);

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

		if (!$indeep) {
			$first_path = null;
		}

		return $listing;
	}



	/**
	 * Recursively copies files from one directory to another.
	 *
	 * @author Matthew McNaney <mcnaney at gmail dot com>
	 */
	public function copy_directory($source_directory, $dest_directory, $overwrite=true, $hidden=false) {
		PHPWS_File::appendSlash($source_directory);
		PHPWS_File::appendSlash($dest_directory);

		if (!is_dir($dest_directory)) {
			if(!@mkdir($dest_directory)) {
				PHPWS_Error::log(PHPWS_DIR_CANT_CREATE, 'core', 'PHPWS_File::copy_directory', $dest_directory);
				return false;
			}
			@chmod($dest_directory, 0755);
		}

		if (!is_writable($dest_directory)) {
			PHPWS_Error::log(PHPWS_DIR_NOT_WRITABLE, 'core', 'PHPWS_File::copy_directory', $dest_directory);
			return false;
		}

		$source_files = scandir($source_directory);
		if (empty($source_files)) {
			return true;
		}

		foreach ($source_files as $file_name) {
			// ignore directories, cvs, and backups
			if ($file_name == '.' || $file_name == '..' || $file_name == 'CVS'
			|| preg_match('/~$/', $file_name) || (!$hidden && preg_match('/^\./', $file_name))) {
				continue;
			}

			$dest_file = $dest_directory . $file_name;
			$src_file  = $source_directory . $file_name;
			if (is_file($src_file)) {
				if (is_file($dest_file)) {
					if (!$overwrite) {
						continue;
					}
					$dest_exists = true;
				} else {
					$dest_exists = false;
				}

				if (!@copy($src_file, $dest_file)) {
					if ($dest_exists && !is_writable($dest_file)) {
						PHPWS_Error::log(PHPWS_FILE_NOT_WRITABLE, 'core', 'PHPWS_File::copy_directory', $dest_file);
					} else {
						PHPWS_Error::log(PHPWS_FILE_NO_COPY, 'core', 'PHPWS_File::copy_directory', $dest_file);
					}
				} else {
					@chmod($dest_file, 0644);
				}
			}  elseif (is_dir($src_file)) {
				if(!PHPWS_File::copy_directory($source_directory . $file_name . '/', $dest_file . '/')) {
					return false;
				}
			}
		}

		return true;
	}

	public function recursiveFileCopy($source_dir, $dest_dir) {
		return PHPWS_File::copy_directory($source_dir, $dest_dir);
	}

	public function writeFile($filename, $text, $allowOverwrite=false, $errorReport=false)
	{
		if (!$allowOverwrite) {
			if (@is_writable($filename)) {
				return false;
			}
		}

		return file_put_contents($filename, $text);
	}// END FUNC writeFile()


	public function readFile($filename, $error=NULL)
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
	 * @param    boolean $overwrite             If true overwrite any file in the destination directory
	 * @param    boolean $fatal                 If true echo an error if the file does not exist.
	 * @return   boolean true on success, false on failure
	 * @access   public
	 */
	public function fileCopy($file_origin, $destination_directory, $file_destination, $overwrite, $fatal)
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
						return true;
					} else {
						return false;
					}
				}
			} else {
				if ($copy_file = @copy($file_origin, $destination_directory . $file_destination)) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if($copy_file = @copy($file_origin, $destination_directory . $file_destination))
			return true;
			else
			return false;
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
	public function makeDir($pathname, $permissions=NULL)
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


	public function _imageCopy($path, $file_type)
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

	public function _writeImageCopy($resampled_image, $dest_dir, $file_type)
	{
		$result = false;

		if ( $file_type == 'image/png'|| $file_type == 'image/x-png' ) {
			$result = imagepng($resampled_image, $dest_dir);
		} elseif ($file_type == 'image/gif') {
			$result = imagegif($resampled_image, $dest_dir);
		} elseif ( $file_type == 'image/jpeg' || $file_type == 'image/pjpeg' ||
		$file_type == 'image/jpg' ) {
			$result = imagejpeg($resampled_image, $dest_dir);
		}
		return $result;
	}

	public function _resampleImage($new_width, $new_height)
	{
		if(PHPWS_File::chkgd2()) {
			$resampled_image = imagecreatetruecolor($new_width, $new_height);
			imagealphablending($resampled_image, false);
			imagesavealpha($resampled_image, true);
		} else {
			$resampled_image = imagecreate($new_width, $new_height);
		}
		return $resampled_image;
	}


	public function rotateImage($source_dir, $dest_dir, $degrees)
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

		$source = PHPWS_File::_imageCopy($source_dir, $file_type);
		$rotate = imagerotate($source, $degrees, 0);

		$result = PHPWS_File::_writeImageCopy($rotate, $dest_dir, $file_type);

		if (!$result) {
			imagedestroy($rotate);
			return false;
		}

		chmod($dest_dir, 0644);
		imagedestroy($rotate);
		return $result;
	}


	public function cropPercent($source_dir, $dest_dir, $percentage, $origin=5)
	{
		if ($percentage > 99) {
			return false;
		}

		$size = getimagesize($source_dir);
		if (empty($size)) {
			return false;
		}

		$width     = & $size[0];
		$height    = & $size[1];
		$new_width = round($width * ((int)$percentage / 100));
		$new_height = round($height * ((int)$percentage / 100));

		return PHPWS_File::cropImage($source_dir, $dest_dir, $new_width, $new_height, $origin);
	}

	/**
	 * origins : top-left      = 1
	 *           top-center    = 2
	 *           top-right     = 3
	 *           center-left   = 4
	 *           center        = 5
	 *           center-right  = 6
	 *           bottom-left   = 7
	 *           bottom-center = 8
	 *           bottom-right  = 9
	 * percentage : percentage of crop reduction
	 */
	public function cropImage($source_dir, $dest_dir, $new_width, $new_height, $origin=5) {
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

		/*
		 * Can't crop to a higher value
		 */
		if ($new_width > $width) {
			$new_width = $width;
		}

		if ($new_height > $height) {
			$new_height = $height;
		}

		$source_image = PHPWS_File::_imageCopy($source_dir, $file_type);
		$resampled_image = PHPWS_File::_resampleImage($new_width, $new_height);

		$sx = $sy = 0;
		switch ($origin) {
			case 1:
				$sx = 0;
				$sy = 0;
				break;

			case 2:
				$sx = round(($width - $new_width) / 2);
				$sy = 0;
				break;

			case 3:
				$sx = $width - $new_width;
				$sy = 0;
				break;

			case 4:
				$sx = 0;
				$sy = round(($height - $new_height) / 2);
				break;

			default:
			case 5:
				$sx = round(($width - $new_width) / 2);
				$sy = round(($height - $new_height) / 2);
				break;

			case 6:
				$sx = ($width - $new_width);
				$sy = round(($height - $new_height) / 2);
				break;

			case 7:
				$sx = 0;
				$sy = $height - $new_height;
				break;

			case 8:
				$sx = round(($width - $new_width) / 2);
				$sy = $height - $new_height;
				break;

			case 9:
				$sx = $width - $new_width;
				$sy =  $height - $new_height;
				break;

		}

		imagecopyresampled($resampled_image,  $source_image,  0, 0, $sx, $sy,
		$new_width, $new_height, $new_width, $new_height);

		imagedestroy($source_image);

		$result = PHPWS_File::_writeImageCopy($resampled_image, $dest_dir, $file_type);

		if (!$result) {
			imagedestroy($resampled_image);
			return false;
		}

		chmod($dest_dir, 0644);
		imagedestroy($resampled_image);
		return true;
	}


	/**
	 * Scales an image down to smaller than the max_width and max_height.
	 * You cannot scale an image to a higher resolution.
	 */
	public function scaleImage($source_dir, $dest_dir, $max_width, $max_height)
	{
		if (empty($max_width) || empty($max_height)) {
			return false;
		}

		if (!extension_loaded('gd')) {
			if (!dl('gd.so')) {
				@copy(PHPWS_SOURCE_DIR . 'core/img/nogd.png', $dest_dir);
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

		if ($width <= $max_width && $height <= $max_height) {
			if ($source_dir != $dest_dir) {
				return @copy($source_dir, $dest_dir);
			} else {
				return true;
			}
		}

		if ($width >= $height) {
			$diff = $max_width / $width;
			$new_width = $max_width;
			$new_height = round($height * $diff);
		}

		if ($width < $height || $new_height > $max_height) {
			$diff = $max_height / $height;
			$new_height = $max_height;
			$new_width = round($width * $diff);
		}

		// Leaving for testing
		/*
		printf('<hr>w=%s h=%s<br>mw=%s mh=%s<br>nw=%s nh=%s<hr>',
		$width, $height, $max_width, $max_height,
		$new_width, $new_height);
		*/

		$source_image = PHPWS_File::_imageCopy($source_dir, $file_type);
		$resampled_image = PHPWS_File::_resampleImage($new_width, $new_height);

		imagecopyresampled($resampled_image,  $source_image,  0, 0, 0, 0,
		$new_width, $new_height, $width, $height);

		imagedestroy($source_image);


		$result = PHPWS_File::_writeImageCopy($resampled_image, $dest_dir, $file_type);

		if (!$result) {
			imagedestroy($resampled_image);
			return false;
		}

		chmod($dest_dir, 0644);
		imagedestroy($resampled_image);
		return true;
	}

	/**
	 * Backward compatibility
	 */
	public function resizeImage($source_dir, $dest_dir, $new_width, $new_height, $force_png=false) {
		return PHPWS_File::scaleImage($source_dir, $dest_dir, $new_width, $new_height);
	}

	/**
	 * Backward compatibility
	 */
	public function makeThumbnail($fileName, $directory, $tndirectory, $maxWidth=125, $maxHeight=125, $replaceFile=false) {
		$source_dir = $directory . $fileName;
		$new_file   = preg_replace('/\.(jpg|jpeg|gif|png)$/i', '_tn.\\1', $fileName);
		$dest_dir   = $tndirectory . $new_file;
		if (!PHPWS_File::scaleImage($source_dir, $dest_dir, $maxWidth, $maxHeight)) {
			return false;
		} else {
			$size = getimagesize($dest_dir);
			return array($new_file, $size[0], $size[1]);
		}
	}

	public function rmdir($dir)
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
						return false;
					}
				}
			}
			closedir($handle);

			$result = @rmdir($dir);
			if (!$result) {
				PHPWS_Error::log(PHPWS_DIR_DELETE_DENIED, 'core', 'PHPWS_File::rmdir', $dir);
				return false;
			}

			return true;
		} else {
			return false;
		}
	}// END FUNC rmdir()


	public function chkgd2()
	{
		if(function_exists('gd_info')) {
			$gdver = gd_info();
			if(strpos($gdver['GD Version'], '1.') != false) {
				return false;
			} else {
				return true;
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
				return false;
			}
			else {
				return true;
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
	public function nameToSafe($name, $maxlen=250)
	{
		$noalpha = 'ÁÉÍÓÚÝáéíóúýÂÊÎÔÛâêîôûÀÈÌÒÙàèìòùÄËÏÖÜäëïöüÿÃãÕõÅåÑñÇç@°ºª';
		$alpha   = 'AEIOUYaeiouyAEIOUaeiouAEIOUaeiouAEIOUaeiouyAaOoAaNnCcaooa';

		$name = substr($name, 0, $maxlen);
		$name = strtr($name, $noalpha, $alpha);
		// not permitted chars are replaced with "_"
		return preg_replace('/[^\w\.]/i', '_', $name);
	}


	public function getFileExtension($filename)
	{
		if (!strpos($filename, '.')) {
			return null;
		}
		$aFile = explode('.', $filename);
		return strtolower(array_pop($aFile));
	}

	public static function appendSlash(&$directory)
	{
		if (!preg_match('/\/$/', $directory)) {
			$directory .= '/';
		}
	}

	public function getMimeType($file_path)
	{
		$mime_type = null;
		if (function_exists('finfo_open')) {
			if (!$finfo = finfo_open(FILEINFO_MIME)) {
				return false;
			}

			if (!$mime_type = finfo_file($finfo, $file_path)) {
				return false;
			}
			return $mime_type;
		} else {
			if (function_exists('mime_content_type')) {
				$mime_type = mime_content_type($file_path);
			}

			if (empty($mime_type)) {
				require_once 'Compat/Function/mime_content_type.php';
				$mime_type = php_compat_mime_content_type($file_path);
			}

			return $mime_type;
		}
	}

	public function checkMimeType($file_path, $ext=null)
	{
		$all_file_type = PHPWS_File::getAllFileTypes();
		if (empty($ext)) {
			$ext = PHPWS_File::getFileExtension($file_path);
		}

		$ext = strtolower($ext);

		if (@$file_info = $all_file_type[$ext]) {
			$mime_type = PHPWS_File::getMimeType($file_path);
			if (function_exists('finfo_open')) {
				foreach ($file_info['fi'] as $fi) {
					if (preg_match("@$fi@i", $mime_type)) {
						return true;
					}
				}
			}

			if (empty($mime_type) && $mct['bl']) {
				return true;
			}
			
			foreach ($file_info['mct'] as $mct) {
				if (preg_match("@^$mct@i", $mime_type)) {
					return true;
				}
			}
			return false;
		} else {
			return false;
		}
	}

	public function getVbType($ext)
	{
		$ext = trim(strtolower($ext));
		if (strpos($ext, '.')) {
			$ext = PHPWS_File::getFileExtension($ext);
		}
		$all_types = PHPWS_File::getAllFileTypes();

		if (!isset($all_types[$ext])) {
			return _('Unknown type');
		} else {
			return $all_types[$ext]['vb'];
		}
	}

	public function getAllFileTypes()
	{
		static $all_file_types = null;
		if (empty($all_file_types)) {
			include PHPWS_SOURCE_DIR . 'core/inc/file_types.php';
		}

		return $all_file_types;
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
