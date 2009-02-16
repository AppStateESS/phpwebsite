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
 * File Name: connector.php
 * 	This is the File Manager Connector for PHP.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

ob_start() ;

include('config.php') ;
include('util.php') ;
include('io.php') ;
include('basexml.php') ;
include('commands.php') ;


DoResponse($Config) ;

function DoResponse($Config)
{
    if ( !$Config['Enabled'] )
	SendError( 1, 'This connector is disabled. Please check the "editor/filemanager/browser/default/connectors/php/config.php" file' ) ;

    // Get the "UserFiles" path.
    $GLOBALS["UserFilesPath"] = '' ;

    // Global the subdirectories for the media types
    if (isSet ($Config['Subdirectory'])) {
	$GLOBALS['Subdirectory'] = $Config['Subdirectory'] ;
    }

    if ( isset( $Config['UserFilesPath'] ) )
	$GLOBALS["UserFilesPath"] = $Config['UserFilesPath'] ;
    else if ( isset( $_GET['ServerPath'] ) )
	$GLOBALS["UserFilesPath"] = $_GET['ServerPath'] ;
    else
	$GLOBALS["UserFilesPath"] = '/UserFiles/' ;

    if ( ! ereg( '/$', $GLOBALS["UserFilesPath"] ) )
	$GLOBALS["UserFilesPath"] .= '/' ;

    if ( strlen( $Config['UserFilesAbsolutePath'] ) > 0 ) 
        {
            $GLOBALS["UserFilesDirectory"] = $Config['UserFilesAbsolutePath'] ;

            if ( ! ereg( '/$', $GLOBALS["UserFilesDirectory"] ) )
		$GLOBALS["UserFilesDirectory"] .= '/' ;
        }
    else
        {
            // Map the "UserFiles" path to a local directory.
            $GLOBALS["UserFilesDirectory"] = GetRootPath() . $GLOBALS["UserFilesPath"] ;
        }

    if (!isset($_GET)) {
        global $_GET;
    }
    if ( !isset( $_GET['Command'] ) || !isset( $_GET['Type'] ) || !isset( $_GET['CurrentFolder'] ) )
        return ;

    // Get the main request informaiton.
    $sCommand		= $_GET['Command'] ;
    $sResourceType	= $_GET['Type'] ;
    $sCurrentFolder	= GetCurrentFolder() ;

    // Check if it is an allowed command
    if ( ! IsAllowedCommand( $sCommand ) )
        SendError( 1, 'The "' . $sCommand . '" command isn\'t allowed' ) ;

    // Check if it is an allowed type.
    if ( !IsAllowedType( $sResourceType ) )
        SendError( 1, 'Invalid type specified' ) ;

    // File Upload doesn't have to Return XML, so it must be intercepted before anything.
    if ( $sCommand == 'FileUpload' )
	{
            FileUpload( $sResourceType, $sCurrentFolder, $sCommand ) ;
            return ;
	}

    CreateXmlHeader( $sCommand, $sResourceType, $sCurrentFolder ) ;

    // Execute the required command.
    switch ( $sCommand )
	{
        case 'GetFolders' :
            GetFolders( $sResourceType, $sCurrentFolder ) ;
            break ;
        case 'GetFoldersAndFiles' :
            GetFoldersAndFiles( $sResourceType, $sCurrentFolder ) ;
            break ;
        case 'CreateFolder' :
            CreateFolder( $sResourceType, $sCurrentFolder ) ;
            break ;
	}

    CreateXmlFooter() ;

    exit ;

}
?>