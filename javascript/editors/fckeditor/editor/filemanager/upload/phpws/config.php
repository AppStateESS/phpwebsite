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
 * 	Configuration file for the PHP File Uploader.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

global $Config ;

// SECURITY: You must explicitelly enable this "uploader". 
$Config['Enabled'] = true ;

// Path to uploaded files relative to the document root.
if ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) {
    $prefix = 'https://';
 } else {
    $prefix = 'http://';
 }

$home_url = $prefix . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$home_url = substr($home_url, 0, strpos($home_url, 'javascript/editors/'));

$Config['UserFilesPath'] = $home_url ;	// Set to / if you want filebrowsing across the whole site directory

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Usefull if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$current_dir = getcwd();
$Config['UserFilesAbsolutePath'] = substr($current_dir, 0, strpos($current_dir, 'javascript/editors/'));

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']	= array('php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','com','dll','vbs','js','reg','cgi') ;
$Config['Subdirectory']['File']	= 'files/' ;

$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;
$Config['Subdirectory']['Image']	= 'images/' ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;
$Config['Subdirectory']['Flash']	= 'media/' ;

?>