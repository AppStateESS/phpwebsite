<?php 
/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: config.php
 * 	Configuration file for the File Manager Connector for PHP.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

global $Config ;

// SECURITY: You must explicitly enable this "connector". (Set it to "true").
$Config['Enabled'] = true ;

// Path to user files relative to the document root.
if ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) {
    $prefix = 'https://';
 } else {
    $prefix = 'http://';
 }

$home_url = $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$home_url = substr($home_url, 0, strpos($home_url, 'javascript/editors/'));

$Config['UserFilesPath'] = $home_url ;	// Set to / if you want filebrowsing across the whole site directory

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Useful if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$current_dir = getcwd();
$Config['UserFilesAbsolutePath'] = substr($current_dir, 0, strpos($current_dir, 'javascript/editors/'));

// Set to $_SERVER['DOCUMENT_ROOT'] if you want filebrowsing across the whole site

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

// What to do if a file being uploaded has the same name as an existing file on the server
// 	'renameold' - (default behaviour) backs up the version on the server to the same name + timestamp appended to the filename (after the extension)
//	'overwrite' - overwrites the version on the server with the same name
// 	'newname' - gives the uploaded file a new name (this was the (unconfigurable) behaviour in FCKeditor2.2)
// 	false - generates an error so that the file uploading fails
$Config['filenameClashBehaviour'] = 'renameold';

// In the following groupings:
//		'Subdirectory' is the subdirectory under the main 'UserFilesPath'
//			e.g. 'File/'
//			or leave it blank as '' to use the main UserFilesPath directory (i.e. the user can add files across the whole site)
//		'Regexp' ereg-style regexp which the name must validate to
//			This regexp applies to the part BEFORE the dot + file extension
//			e.g. '^([-_a-zA-Z0-9]{1,25})$'   (which would be sensible for best practice)
//			or leave it blank as '' for no checking

$Config['Subdirectory']['File']	= 'files/' ;
$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']		= array('php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','com','dll','vbs','js','reg','cgi') ;
$Config['Regexp']['File']	= '' ;

$Config['Subdirectory']['Image']	= 'images/' ;
$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;
$Config['Regexp']['Image']	= '' ;

$Config['Subdirectory']['Flash']	= 'media/' ;
$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;
$Config['Regexp']['Flash']	= '' ;

$Config['Subdirectory']['Media']	= 'media/' ;
$Config['AllowedExtensions']['Media']	= array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['Media']	= array() ;
$Config['Regexp']['Media']	= '' ;

?>