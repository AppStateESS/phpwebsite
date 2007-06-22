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
 * File Name: io.php
 * 	This is the File Manager Connector for PHP.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

function GetUrlFromPath( $resourceType, $folderPath )
{
	return $GLOBALS["UserFilesPath"] . GetResourceTypeSubdirectory ( $resourceType ) . RemoveFromStart ( $folderPath , '/' );
}

function RemoveExtension( $fileName )
{
	return substr( $fileName, 0, strrpos( $fileName, '.' ) ) ;
}

function ServerMapFolder( $resourceType, $folderPath )
{
	// Get the resource type directory.
	$sResourceTypePath = $GLOBALS["UserFilesDirectory"] . GetResourceTypeSubdirectory ( $resourceType );

	// Ensure that the directory exists.
	CreateServerFolder( $sResourceTypePath ) ;

	// Return the resource type directory combined with the required path.
	return $sResourceTypePath . RemoveFromStart( $folderPath, '/' ) ;
}


// Function to determine the directory where the files for this resource type are located
function GetResourceTypeSubdirectory ( $resourceType )
{
	// Return the empty string if no resource type specified, i.e. don't go down into any subdirectory
	if ($resourceType == '') {return '';}
	
	// Use the configured value if it exists; NB array_key_exists is used rather than isSet to allow empty values
	if (isSet ($GLOBALS['Subdirectory']) && array_key_exists ($resourceType, $GLOBALS['Subdirectory'])) {
		
		// If the value is empty, don't add a slash to the empty string, and return that
		if ($GLOBALS['Subdirectory'][$resourceType] == '') {return '';}
		
		// Otherwise ensure the subdirectory is slash-terminated, and return that
		return RemoveFromEnd( $GLOBALS['Subdirectory'][$resourceType], '/' ) . '/';
	}
	
	// Otherwise default to the resource type name itself as the directory name
	return $resourceType . '/';
}

function GetParentFolder( $folderPath )
{
	$sPattern = "-[/\\\\][^/\\\\]+[/\\\\]?$-" ;
	return preg_replace( $sPattern, '', $folderPath ) ;
}

function CreateServerFolder( $folderPath )
{
	// Ensure the folder path has no double-slashes, or mkdir may fail on certain platforms
	while (strpos ($folderPath, '//') !== false)
	{
		$folderPath = str_replace( '//', '/', $folderPath ) ;
	}
	
	$sParent = GetParentFolder( $folderPath ) ;

	// Check if the parent exists, or create it.
	if ( !file_exists( $sParent ) )
	{
		$sErrorMsg = CreateServerFolder( $sParent ) ;
		if ( $sErrorMsg != '' )
			return $sErrorMsg ;
	}

	if ( !file_exists( $folderPath ) )
	{
		// Turn off all error reporting.
		error_reporting( 0 ) ;
		// Enable error tracking to catch the error.
		ini_set( 'track_errors', '1' ) ;

		// To create the folder with 0777 permissions, we need to set umask to zero.
		$oldumask = umask(0) ;
		mkdir( $folderPath, 0777 ) ;
		umask( $oldumask ) ;

		$sErrorMsg = $php_errormsg ;

		// Restore the configurations.
		ini_restore( 'track_errors' ) ;
		ini_restore( 'error_reporting' ) ;

		return $sErrorMsg ;
	}
	else
		return '' ;
}

function GetRootPath()
{
	$sRealPath = realpath( './' ) ;

	$sSelfPath = $_SERVER['PHP_SELF'] ;
	$sSelfPath = substr( $sSelfPath, 0, strrpos( $sSelfPath, '/' ) ) ;

	return substr( $sRealPath, 0, strlen( $sRealPath ) - strlen( $sSelfPath ) ) ;
}
?>