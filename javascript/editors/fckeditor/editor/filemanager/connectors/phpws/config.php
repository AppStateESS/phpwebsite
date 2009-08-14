<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2009 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the File Manager Connector for PHP.
 */

global $Config ;
// Path to user files relative to the document root.
if ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) {
    $prefix = 'https://';
} else {
    $prefix = 'http://';
}

$home_url = $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$home_url = substr($home_url, 0, strpos($home_url, 'javascript/editors/'));

$Config['UserFilesPath'] = $home_url ;  // Set to / if you want filebrowsing across the whole site directory

$current_dir = getcwd();
$Config['UserFilesAbsolutePath'] = substr($current_dir, 0, strpos($current_dir, 'javascript/editors/'));

// SECURITY: You must explicitly enable this "connector". (Set it to "true").
// WARNING: don't just set "$Config['Enabled'] = true ;", you must be sure that only
//		authenticated users can access this file or use some kind of session checking.
require_once $Config['UserFilesAbsolutePath'] . 'config/core/config.php';
define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
session_name(SESSION_NAME);
session_start();

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Useful if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
if (@$_SESSION['FCK_Allow']) {
    $Config['Enabled'] = true;
} else {
    $Config['Enabled'] = false;
}


if ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) {
    $prefix = 'https://';
} else {
    $prefix = 'http://';
}

$home_url = $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$home_url = substr($home_url, 0, strpos($home_url, 'javascript/editors/'));
// Path to user files relative to the document root.
$Config['UserFilesPath'] = $home_url ;

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Useful if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$current_dir = getcwd();
$Config['UserFilesAbsolutePath'] = substr($current_dir, 0, strpos($current_dir, 'javascript/editors/'));

// Due to security issues with Apache modules, it is recommended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

// Perform additional checks for image files.
// If set to true, validate image size (using getimagesize).
$Config['SecureImageUploads'] = true;

// What the user can do with this connector.
$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFolders', 'GetFoldersAndFiles', 'CreateFolder') ;

// Allowed Resource Types.
$Config['ConfigAllowedTypes'] = array('File', 'Image', 'Flash', 'Media') ;

// For security, HTML is allowed in the first Kb of data for files having the
// following extensions only.
$Config['HtmlExtensions'] = array("html", "htm", "xml", "xsd", "txt", "js") ;

// After file is uploaded, sometimes it is required to change its permissions
// so that it was possible to access it at the later time.
// If possible, it is recommended to set more restrictive permissions, like 0755.
// Set to 0 to disable this feature.
// Note: not needed on Windows-based servers.
$Config['ChmodOnUpload'] = 0755 ;

// See comments above.
// Used when creating folders that does not exist.
$Config['ChmodOnFolderCreate'] = 0755 ;

/*
	Configuration settings for each Resource Type

	- AllowedExtensions: the possible extensions that can be allowed.
		If it is empty then any file type can be uploaded.
	- DeniedExtensions: The extensions that won't be allowed.
		If it is empty then no restrictions are done here.

	For a file to be uploaded it has to fulfill both the AllowedExtensions
	and DeniedExtensions (that's it: not being denied) conditions.

	- FileTypesPath: the virtual folder relative to the document root where
		these resources will be located.
		Attention: It must start and end with a slash: '/'

	- FileTypesAbsolutePath: the physical path to the above folder. It must be
		an absolute path.
		If it's an empty string then it will be autocalculated.
		Useful if you are using a virtual directory, symbolic link or alias.
		Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
		Attention: The above 'FileTypesPath' must point to the same directory.
		Attention: It must end with a slash: '/'

	 - QuickUploadPath: the virtual folder relative to the document root where
		these resources will be uploaded using the Upload tab in the resources
		dialogs.
		Attention: It must start and end with a slash: '/'

	 - QuickUploadAbsolutePath: the physical path to the above folder. It must be
		an absolute path.
		If it's an empty string then it will be autocalculated.
		Useful if you are using a virtual directory, symbolic link or alias.
		Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
		Attention: The above 'QuickUploadPath' must point to the same directory.
		Attention: It must end with a slash: '/'

	 	NOTE: by default, QuickUploadPath and QuickUploadAbsolutePath point to
	 	"userfiles" directory to maintain backwards compatibility with older versions of FCKeditor.
	 	This is fine, but you in some cases you will be not able to browse uploaded files using file browser.
	 	Example: if you click on "image button", select "Upload" tab and send image
	 	to the server, image will appear in FCKeditor correctly, but because it is placed
	 	directly in /userfiles/ directory, you'll be not able to see it in built-in file browser.
	 	The more expected behaviour would be to send images directly to "image" subfolder.
	 	To achieve that, simply change
			$Config['QuickUploadPath']['Image']			= $Config['UserFilesPath'] ;
			$Config['QuickUploadAbsolutePath']['Image']	= $Config['UserFilesAbsolutePath'] ;
		into:
			$Config['QuickUploadPath']['Image']			= $Config['FileTypesPath']['Image'] ;
			$Config['QuickUploadAbsolutePath']['Image'] 	= $Config['FileTypesAbsolutePath']['Image'] ;

*/


$Config['AllowedExtensions']['File']	= array('doc', 'txt') ;
$Config['DeniedExtensions']['File']		= array('php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','com','dll','vbs','js','reg','cgi') ;
$Config['FileTypesPath']['File']		= $_SESSION['FCK_FILE_URL'];
$Config['FileTypesAbsolutePath']['File']= $_SESSION['FCK_FILE_DIRECTORY'];
$Config['QuickUploadPath']['File']		= $_SESSION['FCK_FILE_URL'];
$Config['QuickUploadAbsolutePath']['File']= $_SESSION['FCK_FILE_DIRECTORY'];

$Config['AllowedExtensions']['Image']	= array('bmp','gif','jpeg','jpg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;
$Config['FileTypesPath']['Image']		= $_SESSION['FCK_IMAGE_URL'];
$Config['FileTypesAbsolutePath']['Image']= $_SESSION['FCK_IMAGE_DIRECTORY'];
$Config['QuickUploadPath']['Image']		= $_SESSION['FCK_IMAGE_URL'];
$Config['QuickUploadAbsolutePath']['Image']= $_SESSION['FCK_IMAGE_DIRECTORY'];

$Config['AllowedExtensions']['Flash']	= array('swf','flv') ;
$Config['DeniedExtensions']['Flash']	= array() ;
$Config['FileTypesPath']['Flash']		= $_SESSION['FCK_MEDIA_URL'];
$Config['FileTypesAbsolutePath']['Flash']= $_SESSION['FCK_MEDIA_DIRECTORY'];
$Config['QuickUploadPath']['Flash']		= $_SESSION['FCK_MEDIA_URL'];
$Config['QuickUploadAbsolutePath']['Flash']= $_SESSION['FCK_MEDIA_DIRECTORY'];

$Config['AllowedExtensions']['Media']	= array('aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm', 'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv') ;
$Config['DeniedExtensions']['Media']	= array() ;
$Config['FileTypesPath']['Media']		= $_SESSION['FCK_MEDIA_URL'];
$Config['FileTypesAbsolutePath']['Media']= $_SESSION['FCK_MEDIA_DIRECTORY'];
$Config['QuickUploadPath']['Media']		= $_SESSION['FCK_MEDIA_URL'];
$Config['QuickUploadAbsolutePath']['Media']= $_SESSION['FCK_MEDIA_DIRECTORY'];

?>
