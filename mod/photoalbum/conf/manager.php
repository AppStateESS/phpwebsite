<?php

/**
 * @version $Id: manager.php 10 2006-06-06 12:36:51Z matt $
 * @author  Steven Levin <steven at NOSPAM tux[dot]appstate[dot]edu>
 */

/* Labels */
$label     = _('Name');
$editor    = _('Editor');
$updated   = _('Updated');
$desc      = _('Description');
$thumbnail = _('Thumbnail');
$short     = _('Short');
$hidden    = _('Hidden');

$lists = array('albums'     => 'approved=\'1\'',
	       'description'=> 'blurb IS NULL OR blurb=\'\'');

$templates = array('albums'     => 'albums',
		   'description'=> 'description');

$tables = array('albums'     => 'mod_photoalbum_albums',
		'description'=> 'mod_photoalbum_photos');

$albumsColumns = array('label'  => $label,
		       'blurb1' => $desc,
		       'updated'=> $updated,
		       'id'     => NULL,
		       'blurb0' => NULL,
		       'image'  => NULL);

$albumsActions = array();

$albumsPermissions = array();

$albumsPaging = array('op'      => 'PHPWS_AlbumManager_op=list',
		      'limit'   => 10,
		      'section' => 1,
		      'limits'  => array(5,10,20,50),
		      'back'    => '&#60;&#60;',
		      'forward' => '&#62;&#62;');

$descriptionColumns = array('thumbnail' => $thumbnail,
			    'label'     => $short,
			    'updated'   => $updated,
			    'hidden'    => $hidden,
			    'id'        => NULL);

$descriptionActions = array();

$descriptionPermissions = array();

$descriptionPaging = array('op'      => 'PHPWS_Album_op=desc',
			   'limit'   => 10,
			   'section' => 1,
			   'limits'  => array(5,10,20,50),
			   'back'    => '&#60;&#60;',
			   'forward' => '&#62;&#62;');

?>